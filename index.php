<?php
session_start();

/* 
// Comentado temporalmente para que te deje probar el admin
if (isset($_COOKIE['cookie_clienta_id']) && isset($_COOKIE['cookie_clienta_nombre'])) {
    $_SESSION['clienta_logged'] = true;
    $_SESSION['clienta_id'] = $_COOKIE['cookie_clienta_id'];
    $_SESSION['nombre_clienta'] = $_COOKIE['cookie_clienta_nombre'];
    
    header("Location: clienta.php");
    exit();
}
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mariana Nails Studio - Acceso</title>
    
    <!-- Fuentes elegantes de Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Separado -->
    <link rel="stylesheet" href="index.css">
</head>
<body class="login-body">

    <div class="login-card-luxury">
        <!-- Logo de Mariana Nails -->
        <div class="logo-container">
            <img src="Logo.png" alt="Mariana Nails Studio" class="brand-logo">
        </div>
        
        <p class="login-subtitle">Ingresa tus datos para acceder a tu experiencia</p>

        <!-- Formulario para la Clienta apuntando a clienta.php por POST -->
        <form action="clienta.php" method="POST">
            <!-- Campo Nombre -->
                    <div class="form-group-luxury">
                <label for="nombre">Nombre y Apellido</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Sofía Pérez" required>
                </div>
            </div>

            <!-- Campo Fecha de Nacimiento -->
            <div class="form-group-luxury">
                <label for="fnacimiento">Fecha de Nacimiento</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="date" id="fnacimiento" name="fnacimiento" required>
                </div>
            </div>

            <button type="submit" class="btn-luxury">Entrar a mi panel</button>
        </form>

        <div class="luxury-divider">
            <span>o acceso interno</span>
        </div>

        <!-- Botón independiente para el Administrador -->
        <button type="button" class="btn-admin-luxury" onclick="window.location.href='admin.php'">
            <i class="fa-solid fa-key"></i> Panel de Administración
        </button>
    </div>

</body>
</html>