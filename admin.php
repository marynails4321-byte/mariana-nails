<?php
session_start();
require_once 'conexion.php';

$error = '';
$mensaje_exito = '';

// Procesar el inicio de sesión del administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password_ingresada = trim($_POST['password'] ?? '');
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
        <div class="logo-container">
            <img src="Logo.png" alt="Mariana Nails Studio" class="brand-logo">
        </div>
        
        <p class="login-subtitle">Acceso Exclusivo - Administración</p>

        <?php if (!empty($error)): ?>
            <div style="background-color: #fdf2f2; border: 1px solid #f8d7da; color: #a94442; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 15px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

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

// ==========================================
// PROCESAR NUEVA CLIENTA DESDE EL PANEL ADMIN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_clienta') {
    $nuevo_nombre = trim($_POST['nuevo_nombre'] ?? '');
    $nueva_fnac = trim($_POST['nueva_fnac'] ?? '');

    if (!empty($nuevo_nombre) && !empty($nueva_fnac)) {
        try {
            $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, fnacimiento, rol) VALUES (:nombre, :fnac, 'clienta')");
            $stmtInsert->execute(['nombre' => $nuevo_nombre, 'fnac' => $nueva_fnac]);
            $mensaje_exito = "¡Clienta registrada exitosamente!";
        } catch (PDOException $e) {
            $error_db = "Error al registrar la clienta: " . $e->getMessage();
        }
    } else {
        $error_db = "Por favor completa todos los campos para registrar a la clienta.";
    }
}

// ==========================================
// OBTENER DATOS DE LA BASE DE DATOS
// ==========================================
try {
    $stmtClientas = $pdo->query("SELECT * FROM usuarios WHERE rol = 'clienta' ORDER BY id DESC");
    $clientas = $stmtClientas->fetchAll(PDO::FETCH_ASSOC);

    $stmtCitas = $pdo->query("
        SELECT c.*, u.nombre as nombre_clienta 
        FROM citas c 
        JOIN usuarios u ON c.clienta_id = u.id 
        ORDER BY c.fecha_cita DESC
    ");
    $citas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_db = "Error al cargar la base de datos: " . $e->getMessage();
}
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
        
        <div class="agenda-header">
            <div>
                <h2 class="agenda-title">Agenda & Directorio 👑</h2>
                <p class="agenda-subtitle-text">Mariana Nails Studio - Panel de Control Exclusivo</p>
            </div>
            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>

        <?php if (!empty($error_db)): ?>
            <div style="background-color: #fdf2f2; border: 1px solid #f8d7da; color: #a94442; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error_db); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_exito)): ?>
            <div style="background-color: #e2fef0; border: 1px solid #b7ebcc; color: #0f5132; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 15px;">
                <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
        <?php endif; ?>

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
                        <?php if (empty($citas)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--luxury-muted); padding: 20px;">No hay citas agendadas todavía.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($citas as $cita): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($cita['nombre_clienta']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cita['servicio']); ?></td>
                                    <td><?php echo htmlspecialchars($cita['fecha_cita']); ?></td>
                                    <td><span class="badge-status"><?php echo htmlspecialchars($cita['estado']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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

            <!-- Formulario pequeño para registrar clienta desde el Admin -->
            <div style="background: #faf7f2; padding: 15px 20px; border-radius: 8px; border: 1px solid var(--luxury-border); margin-bottom: 20px;">
                <form action="admin.php" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                    <input type="hidden" name="accion" value="crear_clienta">
                    <div style="flex: 2; min-width: 200px;">
                        <label style="display: block; font-size: 0.8rem; color: var(--luxury-muted); margin-bottom: 5px;">Nombre Completo</label>
                        <input type="text" name="nuevo_nombre" placeholder="Ej. Andrea Pérez" required style="width: 100%; padding: 8px; border: 1px solid var(--luxury-border); border-radius: 6px; font-size: 0.9rem;">
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; font-size: 0.8rem; color: var(--luxury-muted); margin-bottom: 5px;">Fecha de Nacimiento</label>
                        <input type="date" name="nueva_fnac" required style="width: 100%; padding: 8px; border: 1px solid var(--luxury-border); border-radius: 6px; font-size: 0.9rem;">
                    </div>
                    <div>
                        <button type="submit" class="btn-luxury" style="padding: 9px 15px; font-size: 0.85rem;"><i class="fa-solid fa-user-plus"></i> Guardar Clienta</button>
                    </div>
                </form>
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
                        <?php if (empty($clientas)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--luxury-muted); padding: 20px;">No hay clientas registradas todavía.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientas as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['fnacimiento']); ?></td>
                                    <td><?php echo htmlspecialchars($c['creado_en']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
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