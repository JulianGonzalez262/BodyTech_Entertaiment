<?php
session_start();

// Vaciar la sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: /bodytech/public/index.php');
exit;
