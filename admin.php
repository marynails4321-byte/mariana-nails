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
<!-- PANEL DE ADMINISTRACIÓN / AGENDA PRINCIPAL -->
<!-- ========================================== -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda & Panel - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body agenda-body-align">

    <div class="agenda-container">
        
        <!-- Cabecera de la Agenda -->
        <div class="agenda-header">
            <div>
                <h2 class="agenda-title">Agenda & Directorio 👑</h2>
                <p class="agenda-subtitle-text">Mariana Nails Studio - Panel de Control Exclusivo</p>
            </div>
            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>

        <!-- Pestañas de Navegación -->
        <div class="agenda-tabs">
            <button class="tab-btn active" onclick="switchTab(event, 'citas-section')"><i class="fa-solid fa-calendar-days"></i> Agenda de Citas</button>
            <button class="tab-btn" onclick="switchTab(event, 'clientas-section')"><i class="fa-solid fa-users"></i> Directorio de Clientas</button>
        </div>

        <!-- SECCIÓN 1: AGENDA DE CITAS -->
        <div id="citas-section" class="tab-content active">
            <div class="section-flex-header">
                <h3 class="section-title">Próximos Turnos Agendados</h3>
                <span class="section-hint">Vista general de la agenda</span>
            </div>

            <div class="table-responsive">
                <table class="luxury-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Clienta</th>
                            <th>Servicio Solicitado</th>
                            <th>Fecha y Hora</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos de ejemplo (se conectarán dinámicamente con PostgreSQL) -->
                        <tr>
                            <td>1</td>
                            <td><strong>Sofía Valdés</strong></td>
                            <td>Estonian Manicure + Soft Gel</td>
                            <td>10 de Jun, 2026 - 15:00</td>
                            <td><span class="badge-status">Confirmada</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><strong>Valentina Gómez</strong></td>
                            <td>Nail Art Minimalista & Kapping</td>
                            <td>12 de Jun, 2026 - 11:30</td>
                            <td><span class="badge-status">Confirmada</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECCIÓN 2: DIRECTORIO DE CLIENTAS -->
        <div id="clientas-section" class="tab-content">
            <div class="section-flex-header">
                <h3 class="section-title">Registro de Clientas</h3>
                <span class="section-hint">Datos de acceso y cumpleaños</span>
            </div>

            <div class="table-responsive">
                <table class="luxury-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Fecha de Nacimiento</th>
                            <th>Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos de ejemplo (se conectarán dinámicamente con PostgreSQL) -->
                        <tr>
                            <td>1</td>
                            <td><strong>Sofía Valdés</strong></td>
                            <td>15 / 04 / 1998</td>
                            <td>06/06/2026</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><strong>Valentina Gómez</strong></td>
                            <td>22 / 09 / 2001</td>
                            <td>07/06/2026</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // Función para alternar entre pestañas de la agenda
        function switchTab(evt, sectionId) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            document.getElementById(sectionId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>