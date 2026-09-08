<?php
session_start();

// 1. Destruir todas las variables de la sesión de PHP
$_SESSION = array();

// 2. Destruir la sesión físicamente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 3. Eliminar las cookies de persistencia del dispositivo (expirándolas en el pasado)
if (isset($_COOKIE['cookie_clienta_id'])) {
    setcookie('cookie_clienta_id', '', time() - 3600, "/");
}
if (isset($_COOKIE['cookie_clienta_nombre'])) {
    setcookie('cookie_clienta_nombre', '', time() - 3600, "/");
}

// 4. Redirigir al inicio (index.php)
header("Location: index.php");
exit();
?>