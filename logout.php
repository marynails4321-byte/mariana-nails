<?php
session_start();

// Destruir sesión de PHP
$_SESSION = array();
session_destroy();

// Eliminar la cookie de persistencia del administrador
if (isset($_COOKIE['cookie_admin_logged'])) {
    setcookie('cookie_admin_logged', '', time() - 3600, "/");
}

// Redirigir al panel de administración (que pedirá contraseña de nuevo)
header("Location: admin.php");
exit();
?>