<?php
session_start();

// Incluimos la conexión centralizada (compatible con Render / PostgreSQL y local)
require_once 'conexion.php';

$error_db = '';
$citas_clienta = [];

// Procesar cuando el formulario es enviado por POST (Inicio de sesión de clienta)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $fnacimiento = trim($_POST['fnacimiento'] ?? '');

    if (!empty($nombre) && !empty($fnacimiento)) {
        try {
            // 1. Verificar si la clienta ya existe en la base de datos
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE LOWER(nombre) = LOWER(:nombre) AND fnacimiento = :fnacimiento AND rol = 'clienta'");
            $stmt->execute(['nombre' => $nombre, 'fnacimiento' => $fnacimiento]);
            $clienta = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($clienta) {
                $_SESSION['clienta_id'] = $clienta['id'];
            } else {
                // 2. Si no existe, se registra automáticamente en la tabla usuarios
                $insertStmt = $pdo->prepare("INSERT INTO usuarios (nombre, fnacimiento, rol) VALUES (:nombre, :fnacimiento, 'clienta') RETURNING id");
                $insertStmt->execute(['nombre' => $nombre, 'fnacimiento' => $fnacimiento]);
                $nuevoUsuario = $insertStmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['clienta_id'] = $nuevoUsuario['id'];
            }

            // Establecer sesión activa
            $_SESSION['nombre_clienta'] = $nombre;
            $_SESSION['clienta_logged'] = true;

            // NUEVO: Guardar cookie por 30 días para recordar el dispositivo y evitar duplicados
            setcookie('cookie_clienta_id', $_SESSION['clienta_id'], time() + (86400 * 30), "/");
            setcookie('cookie_clienta_nombre', $nombre, time() + (86400 * 30), "/");

        } catch (PDOException $e) {
            $error_db = "Error al conectar con la base de datos: " . $e->getMessage();
        }
    } else {
        header("Location: index.php");
        exit();
    }
}

// Seguridad: Si intentan entrar por la URL sin iniciar sesión
if (!isset($_SESSION['clienta_logged']) || $_SESSION['clienta_logged'] !== true) {
    header("Location: index.php");
    exit();
}

$nombre_clienta = $_SESSION['nombre_clienta'];
$clienta_id = $_SESSION['clienta_id'] ?? null;

// ==========================================
// CARGAR LAS CITAS REALES DE ESTA CLIENTA
// ==========================================
try {
    if ($clienta_id) {
        $stmtCitasClienta = $pdo->prepare("
            SELECT * FROM citas 
            WHERE clienta_id = :clienta_id 
            ORDER BY fecha_cita DESC
        ");
        $stmtCitasClienta->execute(['clienta_id' => $clienta_id]);
        $citas_clienta = $stmtCitasClienta->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_db = "Error al cargar tus citas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="clienta.css">
</head>
<body class="login-body agenda-body-align">

    <div class="agenda-container" style="max-width: 800px;">
        
        <!-- Cabecera del Panel de Clienta -->
        <div class="agenda-header">
            <div>
                <h2 class="agenda-title">Bienvenida, <?php echo htmlspecialchars($nombre_clienta); ?> ✨</h2>
                <p class="agenda-subtitle-text">Mariana Nails Studio - Tu espacio de belleza</p>
            </div>
            <a href="logoutc.php" class="logout-link">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Salir
            </a>
        </div>

        <?php if (!empty($error_db)): ?>
            <div style="background-color: #fdf2f2; border: 1px solid #f8d7da; color: #a94442; padding: 10px; border-radius: 8px; font-size: 0.8rem; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error_db); ?>
            </div>
        <?php endif; ?>

        <!-- Tarjeta de Información Principal -->
        <div style="background-color: #faf7f2; padding: 25px; border-radius: 12px; border: 1px solid var(--luxury-border); margin-bottom: 25px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: var(--luxury-dark); margin-top: 0; margin-bottom: 10px;">
                <i class="fa-solid fa-calendar-check" style="color: #c57d0a; margin-right: 8px;"></i> Tus Citas Registradas
            </h3>
            <p style="color: var(--luxury-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                Aquí puedes visualizar el estado de tus turnos agendados en el estudio.
            </p>

            <?php if (empty($citas_clienta)): ?>
                <!-- Estado vacío si no tiene citas aún -->
                <div style="background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid var(--luxury-border); text-align: center; color: var(--luxury-muted);">
                    <i class="fa-regular fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 10px; display: block; color: #c57d0a;"></i>
                    No tienes citas agendadas todavía. ¡Pronto podrás apartar tu espacio!
                </div>
            <?php else: ?>
                <!-- Listado real de citas de la clienta -->
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($citas_clienta as $cita): ?>
                        <div style="background: #ffffff; padding: 15px 20px; border-radius: 8px; border: 1px solid var(--luxury-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <span style="display: block; font-size: 0.8rem; color: var(--luxury-muted);">Servicio</span>
                                <strong style="color: var(--luxury-dark); font-size: 1rem;"><?php echo htmlspecialchars($cita['servicio']); ?></strong>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.8rem; color: var(--luxury-muted);">Fecha y Hora</span>
                                <strong style="color: var(--luxury-dark); font-size: 1rem;"><?php echo htmlspecialchars($cita['fecha_cita']); ?></strong>
                            </div>
                            <div>
                                <span class="badge-status"><?php echo htmlspecialchars($cita['estado']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="agendar.php" class="btn-luxury" style="flex: 1; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
    <i class="fa-solid fa-plus-circle"></i> Agendar Nueva Cita
</a>
            </button>
            <button type="button" class="btn-luxury" style="flex: 1; background: linear-gradient(135deg, #3a2e2b 0%, #2b2d42 100%); color: #ffffff;" onclick="alert('Próximamente: Historial de tus servicios anteriores.');">
                <i class="fa-solid fa-clock-rotate-left"></i> Historial de Turnos
            </button>
        </div>

    </div>

</body>
</html>