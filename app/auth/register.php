<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

// 2. Verificar campos
if (
    empty($_POST['nombres']) ||
    empty($_POST['apellidos']) ||
    empty($_POST['email']) ||
    empty($_POST['password'])
) {
    die('Todos los campos son obligatorios.');
}

$nombres = trim($_POST['nombres']);
$apellidos = trim($_POST['apellidos']);

$nombre = trim($nombres . ' ' . $apellidos);

$email = trim($_POST['email']);
$password = $_POST['password'];


/*
|--------------------------------------------------------------------------
| 3. VERIFICAR CORREOS DE CUENTAS ELIMINADAS
|--------------------------------------------------------------------------
*/

$emailNormalizado = mb_strtolower(trim($email), 'UTF-8');

$stmt = $pdo->prepare("
    SELECT id
    FROM correos_cuentas_eliminadas
    WHERE email = :email
    LIMIT 1
");

$stmt->execute([
    'email' => $emailNormalizado
]);

if ($stmt->fetch()) {
    die('Este correo pertenece a una cuenta eliminada y no puede volver a registrarse.');
}

/*
|--------------------------------------------------------------------------
| 4. VERIFICAR SI EL CORREO YA EXISTE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM usuarios
    WHERE email = :email
    LIMIT 1
");

$stmt->execute([
    'email' => $emailNormalizado
]);

if ($stmt->fetch()) {
    die('Este correo ya está registrado.');
}

$email = $emailNormalizado;


// 4. Crear hash de la contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

// 5. Insertar nuevo cliente
$stmt = $pdo->prepare("
    INSERT INTO usuarios
    (
        nombre,
        nombres,
        apellidos,
        email,
        password,
        rol_id
    )
    VALUES
    (
        :nombre,
        :nombres,
        :apellidos,
        :email,
        :password,
        1
    )
");

$stmt->execute([
    'nombre'     => $nombre,
    'nombres'    => $nombres,
    'apellidos'  => $apellidos,
    'email'      => $email,
    'password'   => $hash
]);

echo "Registro exitoso. Ahora puedes iniciar sesión.";
?>