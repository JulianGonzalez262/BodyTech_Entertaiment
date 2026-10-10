<?php
// admin/admin_auth.php
session_start();

// Verificar sesión de administrador
if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_rol']) ||
    $_SESSION['admin_rol'] !== 'admin'
) {
    header("Location: login.php");
    exit;
}