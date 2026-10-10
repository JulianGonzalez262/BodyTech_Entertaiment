<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['afiliado'])) {
    die('Proceso inválido.');
}

$af = $_SESSION['afiliado'];


/*
|--------------------------------------------------------------------------
| BLOQUEAR CORREOS DE CUENTAS ELIMINADAS
|--------------------------------------------------------------------------
*/

if (empty($af['datos']['email'])) {
    die('No se encontró el correo del afiliado.');
}

$emailAfiliado = mb_strtolower(
    trim($af['datos']['email']),
    'UTF-8'
);

$stmt = $pdo->prepare("
    SELECT id
    FROM correos_cuentas_eliminadas
    WHERE email = ?
    LIMIT 1
");

$stmt->execute([
    $emailAfiliado
]);

if ($stmt->fetch()) {
    die('Este correo pertenece a una cuenta eliminada y no puede volver a utilizarse en BodyTech.');
}

/*
|--------------------------------------------------------------------------
| CONTINUAR CON EL PROCESO DE AFILIACIÓN
|--------------------------------------------------------------------------
*/



try {
    $pdo->beginTransaction();

    /* =========================
       1️⃣ USUARIO
    ========================== */
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $af['datos']['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $usuario_id = $user['id'];
    } else {
        $hash = password_hash($af['datos']['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
    INSERT INTO usuarios
        (
            nombre,
            nombres,
            apellidos,
            email,
            password,
            telefono_principal,
            direccion,
            tipo_documento,
            numero_documento,
            rol_id
        )
        VALUES
        (
            :nombre,
            :nombres,
            :apellidos,
            :email,
            :password,
            :telefono_principal,
            :direccion,
            :tipo_documento,
            :numero_documento,
            1
        )
    ");
        $stmt->execute([

        'nombre'               => $af['datos']['nombre'],
        'nombres'              => $af['datos']['nombres'],
        'apellidos'            => $af['datos']['apellidos'],

        'email'                => $af['datos']['email'],
        'password'             => $hash,

        'telefono_principal'   => $af['datos']['telefono'],
        'direccion'            => $af['datos']['direccion'],

        'tipo_documento'       => $af['datos']['tipo_id'],
        'numero_documento'     => $af['datos']['numero_id']

    ]);

        $usuario_id = $pdo->lastInsertId();
    }

    /* =========================
       2️⃣ VALIDACIONES
    ========================== */
    $stmt = $pdo->prepare("
        SELECT id FROM afiliados
        WHERE usuario_id = :id AND estado = 'activo'
        LIMIT 1
    ");
    $stmt->execute(['id' => $usuario_id]);

    if ($stmt->fetch()) {
        throw new Exception('Ya tienes una afiliación activa.');
    }

    $stmt = $pdo->prepare("
        UPDATE afiliados
        SET estado = 'cancelado'
        WHERE usuario_id = :id
    ");
    $stmt->execute(['id' => $usuario_id]);

    /* =========================
       3️⃣ AFILIADO
    ========================== */
    $qr_token = bin2hex(random_bytes(32));

    $fechaInicio = new DateTime();
    $fechaFin = null;

    $plan = strtolower(trim($af['tipo_membresia']));

    if (str_contains($plan, 'anual_promo')) {
        $fechaFin = (clone $fechaInicio)->modify('+1 year');
    } elseif (str_contains($plan, 'prepago_6m')) {
        $fechaFin = (clone $fechaInicio)->modify('+6 months');
    } elseif (str_contains($plan, 'prepago_3m')) {
        $fechaFin = (clone $fechaInicio)->modify('+3 months');
    } elseif (str_contains($plan, 'nocturno')) {
        $fechaFin = (clone $fechaInicio)->modify('+1 month');
    } elseif (str_contains($plan, 'mensual') || str_contains($plan, 'departamental')) {
        $fechaFin = (clone $fechaInicio)->modify('+1 month');
    } elseif (str_contains($plan, 'debito_automatico') && !str_contains($plan, 'debito_flexible')) {
        $fechaFin = (clone $fechaInicio)->modify('+6 months');
    } elseif (str_contains($plan, 'debito_flexible')) {
        $fechaFin = null;
    }

    $stmt = $pdo->prepare("
        INSERT INTO afiliados (
            usuario_id, gym_nombre, ciudad, departamento,
            tipo_membresia, precio,
            fecha_inicio, fecha_fin,
            fecha_nacimiento, genero,
            direccion, telefono, tipo_id, numero_id,
            contacto_emergencia, telefono_emergencia,
            consentimiento_marketing,
            estado, qr_token, created_at
        ) VALUES (
            :usuario_id, :gym_nombre, :ciudad, :departamento,
            :tipo_membresia, :precio,
            :fecha_inicio, :fecha_fin,
            :fecha_nacimiento, :genero,
            :direccion, :telefono, :tipo_id, :numero_id,
            :contacto_emergencia, :telefono_emergencia,
            :consentimiento_marketing,
            'activo', :qr_token, NOW()
        )
    ");

    $stmt->execute([
        'usuario_id'               => $usuario_id,
        'gym_nombre'               => $af['gym_nombre'],
        'ciudad'                   => $af['ciudad'],
        'departamento'             => $af['departamento'],
        'tipo_membresia'           => $af['tipo_membresia'],
        'precio'                   => $af['precio'],
        'fecha_inicio'             => $fechaInicio->format('Y-m-d'),
        'fecha_fin'                => $fechaFin ? $fechaFin->format('Y-m-d') : null,
        'fecha_nacimiento'         => $af['datos']['fecha_nacimiento'],
        'genero'                   => $af['datos']['genero'],
        'direccion'                => $af['datos']['direccion'],
        'telefono'                 => $af['datos']['telefono'],
        'tipo_id'                  => $af['datos']['tipo_id'],
        'numero_id'                => $af['datos']['numero_id'],
        'contacto_emergencia'      => $af['datos']['contacto_emergencia'],
        'telefono_emergencia'      => $af['datos']['telefono_emergencia'],
        'consentimiento_marketing' => $af['datos']['consentimiento_marketing'] ? 1 : 0,
        'qr_token'                 => $qr_token
    ]);

    $afiliado_id = $pdo->lastInsertId();

    /* =========================
       4️⃣ PAGO INICIAL
    ========================== */
    $referencia = 'BT-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $stmt = $pdo->prepare("
        INSERT INTO pagos (
            usuario_id, afiliado_id, tipo_membresia,
            monto, metodo, estado, referencia, gateway, created_at
        ) VALUES (
            :usuario_id, :afiliado_id, :tipo_membresia,
            :monto, 'afiliacion', 'aprobado', :referencia, 'sistema', NOW()
        )
    ");

    $stmt->execute([
        'usuario_id'     => $usuario_id,
        'afiliado_id'    => $afiliado_id,
        'tipo_membresia' => $af['tipo_membresia'],
        'monto'          => $af['precio'],
        'referencia'     => $referencia
    ]);

    /* =========================
       5️⃣ DÉBITO AUTOMÁTICO
    ========================== */
    if ($plan === 'debito_automatico') {

        $stmt = $pdo->prepare("
            INSERT INTO planes_automaticos (
                usuario_id, afiliado_id, tipo_membresia,
                monto, frecuencia,
                fecha_inicio, fecha_fin,
                proxima_ejecucion, estado, created_at
            ) VALUES (
                :usuario_id, :afiliado_id, 'debito_automatico',
                20000, 'semanal',
                :inicio, :fin,
                :proximo, 'activo', NOW()
            )
        ");

        $stmt->execute([
            'usuario_id' => $usuario_id,
            'afiliado_id'=> $afiliado_id,
            'inicio'     => $fechaInicio->format('Y-m-d'),
            'fin'        => (clone $fechaInicio)->modify('+6 months')->format('Y-m-d'),
            'proximo'    => (clone $fechaInicio)->modify('+7 days')->format('Y-m-d')
        ]);
    }

    if ($plan === 'debito_flexible') {

        $stmt = $pdo->prepare("
            INSERT INTO planes_automaticos (
                usuario_id, afiliado_id, tipo_membresia,
                monto, frecuencia,
                fecha_inicio, fecha_fin,
                proxima_ejecucion, estado, created_at
            ) VALUES (
                :usuario_id, :afiliado_id, 'debito_flexible',
                :monto, 'mensual',
                :inicio, NULL,
                :proximo, 'activo', NOW()
            )
        ");

        $stmt->execute([
            'usuario_id' => $usuario_id,
            'afiliado_id'=> $afiliado_id,
            'monto'      => $af['precio'],
            'inicio'     => $fechaInicio->format('Y-m-d'),
            'proximo'    => (clone $fechaInicio)->modify('+1 month')->format('Y-m-d')
        ]);
    }

    $pdo->commit();
/* =========================
   NOTIFICACIÓN AFILIACIÓN
========================= */

$stmt = $pdo->prepare("
    INSERT INTO notificaciones
    (
        usuario_id,
        titulo,
        mensaje,
        tipo
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )
");

$stmt->execute([
    $usuario_id,
    'Afiliación completada',
    'Tu afiliación ha sido registrada correctamente y ya puedes disfrutar de los beneficios de tu membresía.',
    'afiliacion'
]);
    /* =========================
       6️⃣ SESIÓN
    ========================== */
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario'] = [
        'id'     => $usuario_id,
        'email'  => $af['datos']['email'],
        'nombre' => $af['datos']['nombre'],
        'rol'    => 'cliente'
    ];

    unset($_SESSION['afiliado']);

    header('Location: /bodytech/public/afiliacion_exitosa.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die('Error en la afiliación: ' . $e->getMessage());
}

