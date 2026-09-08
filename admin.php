<?php
session_start();

$error = '';

// Procesar el inicio de sesión del administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_ingresada = trim($_POST['password'] ?? '');
    
    // Contraseña única de acceso para Mariana (puedes cambiarla aquí cuando gustes)
    $password_correcta = '4321Mary';

    if ($password_ingresada === $password_correcta) {
        $_SESSION['admin_logged'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = 'Contraseña incorrecta. Intenta nuevamente.';
    }
}

// Si el administrador NO ha iniciado sesión, mostramos el formulario de acceso
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administradora - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
</head>
<body class="login-body">

    <div class="login-card-luxury">
        <!-- Logo del Studio -->
        <div class="logo-container">
            <img src="Logo.png" alt="Mariana Nails Studio" class="brand-logo">
        </div>
        
        <p class="login-subtitle">Acceso Exclusivo - Administración</p>

        <?php if (!empty($error)): ?>
            <div style="background-color: #fdf2f2; border: 1px solid #f8d7da; color: #a94442; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 15px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de contraseña -->
        <form action="admin.php" method="POST">
            <div class="form-group-luxury">
                <label for="password">Contraseña</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
            </div>

            <button type="submit" class="btn-luxury">Ingresar al Panel</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: var(--luxury-muted); text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                <i class="fa-solid fa-arrow-left"></i> Volver al inicio de clientas
            </a>
        </div>
    </div>

</body>
</html>
<?php 
exit();
endif; 
?>

<!-- ========================================== -->
<!-- PANEL DE ADMINISTRACIÓN PRINCIPAL (AUTORIZADO) -->
<!-- ========================================== -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

    <div class="login-card-luxury" style="max-width: 700px; text-align: left;">
        
        <!-- Cabecera del Panel Admin -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--luxury-border); padding-bottom: 20px; margin-bottom: 25px;">
            <div>
                <h2 style="font-family: 'Cormorant Garamond', serif; color: var(--luxury-dark); font-size: 1.8rem; margin: 0;">Panel de Administración 👑</h2>
                <p style="color: var(--luxury-muted); font-size: 0.9rem; margin: 5px 0 0 0;">Mariana Nails Studio - Control General</p>
            </div>
            <a href="logout.php" style="color: #c57d0a; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Salir
            </a>
        </div>

        <!-- Sección de bienvenida y gestión -->
        <div style="background-color: #faf7f2; padding: 20px; border-radius: 12px; border: 1px solid var(--luxury-border); margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; color: var(--luxury-dark); margin-top: 0;"><i class="fa-solid fa-clipboard-list" style="color: #c57d0a; margin-right: 8px;"></i> Gestión de Clientas y Citas</h3>
            <p style="color: var(--luxury-muted); font-size: 0.95rem; line-height: 1.5;">
                Desde aquí podrás supervisar los accesos de las clientas, revisar sus fechas de nacimiento y gestionar los turnos agendados en el estudio.
            </p>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="button" class="btn-luxury" style="flex: 1;" onclick="alert('Sección para ver la lista de clientas conectada a la base de datos.');">
                <i class="fa-solid fa-users"></i> Ver Clientas
            </button>
            <button type="button" class="btn-luxury" style="flex: 1; background: linear-gradient(135deg, #3a2e2b 0%, #2b2d42 100%); color: #ffffff;" onclick="alert('Sección de turnos y citas próximas.');">
                <i class="fa-solid fa-calendar-days"></i> Ver Citas
            </button>
        </div>

    </div>

</body>
</html>