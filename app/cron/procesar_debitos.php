<?php
require_once __DIR__ . '/../config/database.php';

$hoy = new DateTime();


$stmt = $pdo->prepare("
    SELECT *
    FROM planes_automaticos
    WHERE estado = 'activo'
      AND proxima_ejecucion <= CURDATE()
");
$stmt->execute();

$planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($planes as $plan) {

   
    $referencia = 'AUTO-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $stmtPago = $pdo->prepare("
        INSERT INTO pagos (
            usuario_id,
            afiliado_id,
            tipo_membresia,
            monto,
            metodo,
            estado,
            referencia,
            gateway,
            created_at
        ) VALUES (
            :usuario_id,
            :afiliado_id,
            :tipo_membresia,
            :monto,
            'debito_automatico',
            'aprobado',
            :referencia,
            'sistema',
            NOW()
        )
    ");

    $stmtPago->execute([
        'usuario_id'     => $plan['usuario_id'],
        'afiliado_id'    => $plan['afiliado_id'],
        'tipo_membresia' => $plan['tipo_membresia'],
        'monto'          => $plan['monto'],
        'referencia'     => $referencia
    ]);

  
    $siguiente = new DateTime($plan['proxima_ejecucion']);

    if ($plan['frecuencia'] === 'semanal') {
        $siguiente->modify('+7 days');
    } else {
        $siguiente->modify('+1 month');
    }

   
    $estado = 'activo';

    if (
        $plan['fecha_fin'] !== null &&
        $siguiente > new DateTime($plan['fecha_fin'])
    ) {
        $estado = 'finalizado';
    }

    $stmtUpdate = $pdo->prepare("
        UPDATE planes_automaticos
        SET
            ultima_ejecucion = CURDATE(),
            proxima_ejecucion = :proxima,
            estado = :estado
        WHERE id = :id
    ");

    $stmtUpdate->execute([
        'proxima' => $siguiente->format('Y-m-d'),
        'estado'  => $estado,
        'id'      => $plan['id']
    ]);
}
exit(0);