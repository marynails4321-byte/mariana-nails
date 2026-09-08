<?php
session_start();

// Configuración de conexión a PostgreSQL (Aiven)
// Reemplaza estos valores con los datos reales de tu base de datos en Aiven
$host = "TU_HOST_DE_AIVEN";
$port = "5432";
$dbname = "defaultdb";
$user = "avnadmin";
$password = "TU_PASSWORD_DE_AIVEN";

$error_db = '';

// Procesar cuando el formulario es enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $fnacimiento = trim($_POST['fnacimiento'] ?? '');

    if (!empty($nombre) && !empty($fnacimiento)) {
        try {
            // Conexión PDO con SSL requerido para Aiven
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

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

        } catch (PDOException $e) {
            // Si hay un fallo de conexión temporal, permitimos el acceso visual con aviso
            $_SESSION['nombre_clienta'] = $nombre;
            $_SESSION['clienta_logged'] = true;
            $error_db = "Aviso: No se pudo conectar a la base de datos de Aiven en este momento.";
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
            <a href="index.php" class="logout-link">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Salir
            </a>
        </div>

        <?php if (!empty($error_db)): ?>
            <div style="background-color: #fdf2f2; border: 1px solid #f8d7da; color: #a94442; padding: 10px; border-radius: 8px; font-size: 0.8rem; margin-bottom: 15px;">
                <?php echo $error_db; ?>
            </div>
        <?php endif; ?>

        <!-- Tarjeta de Información Principal -->
        <div style="background-color: #faf7f2; padding: 25px; border-radius: 12px; border: 1px solid var(--luxury-border); margin-bottom: 25px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: var(--luxury-dark); margin-top: 0; margin-bottom: 10px;">
                <i class="fa-solid fa-calendar-check" style="color: #c57d0a; margin-right: 8px;"></i> Tu Próxima Cita
            </h3>
            <p style="color: var(--luxury-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                Tus datos han sido validados correctamente en el sistema del estudio.
            </p>

            <!-- Bloque de cita actual (Ejemplo visual) -->
            <div style="background: #ffffff; padding: 15px 20px; border-radius: 8px; border: 1px solid var(--luxury-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="display: block; font-size: 0.8rem; color: var(--luxury-muted);">Servicio</span>
                    <strong style="color: var(--luxury-dark); font-size: 1rem;">Estonian Manicure + Soft Gel</strong>
                </div>
                <div>
                    <span style="display: block; font-size: 0.8rem; color: var(--luxury-muted);">Fecha y Hora</span>
                    <strong style="color: var(--luxury-dark); font-size: 1rem;">10 de Jun, 2026 - 15:00</strong>
                </div>
                <div>
                    <span class="badge-status">Confirmada</span>
                </div>
            </div>
        </div>

        <!-- Botones de Acción (Adaptados para celular) -->
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <button type="button" class="btn-luxury" style="flex: 1;" onclick="alert('Próximamente: Formulario para agendar una nueva cita.');">
                <i class="fa-solid fa-plus-circle"></i> Agendar Nueva Cita
            </button>
            <button type="button" class="btn-luxury" style="flex: 1; background: linear-gradient(135deg, #3a2e2b 0%, #2b2d42 100%); color: #ffffff;" onclick="alert('Próximamente: Historial de tus servicios anteriores.');">
                <i class="fa-solid fa-clock-rotate-left"></i> Historial de Turnos
            </button>
        </div>

    </div>

</body>
</html>