<?php
session_start();
require_once 'conexion.php'; // Asegúrate de tener tu archivo de conexión a la base de datos

$error_login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? ''); // <--- Capturamos el teléfono del formulario
    $fnacimiento = trim($_POST['fnacimiento'] ?? '');

    if (!empty($nombre) && !empty($telefono) && !empty($fnacimiento)) {
        try {
            // Verificar si el usuario ya existe en la base de datos (por nombre y teléfono)
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = :nombre AND rol = 'clienta'");
            $stmt->execute(['nombre' => $nombre]);
            $clienta = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($clienta) {
                // Si ya existe, actualizamos su teléfono y fecha de nacimiento por si cambiaron
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET telefono = :telefono, fnacimiento = :fnacimiento WHERE id = :id");
                $stmtUpdate->execute([
                    'telefono' => $telefono,
                    'fnacimiento' => $fnacimiento,
                    'id' => $clienta['id']
                ]);
                
                $clienta_id = $clienta['id'];
            } else {
                // Si no existe, la registramos como nueva clienta incluyendo el teléfono
                $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, telefono, fnacimiento, rol) VALUES (:nombre, :telefono, :fnacimiento, 'clienta')");
                $stmtInsert->execute([
                    'nombre' => $nombre,
                    'telefono' => $telefono,
                    'fnacimiento' => $fnacimiento
                ]);
                
                $clienta_id = $pdo->lastInsertId();
            }

            // Establecer las variables de sesión para la clienta
            $_SESSION['clienta_logged'] = true;
            $_SESSION['clienta_id'] = $clienta_id;
            $_SESSION['nombre_clienta'] = $nombre;

            // Redirigir al panel de la clienta
            header("Location: clienta.php");
            exit();

        } catch (PDOException $e) {
            $error_login = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        $error_login = "Por favor completa todos los campos obligatorios.";
    }
} else {
    // Si intentan entrar a clienta.php directamente por URL sin enviar el formulario
    if (!isset($_SESSION['clienta_logged']) || $_SESSION['clienta_logged'] !== true) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Clienta - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
</head>
<body class="login-body">
    <div class="login-card-luxury" style="max-width: 600px; text-align: center;">
        <h2 style="font-family: 'Cormorant Garamond', serif; color: var(--luxury-dark);">¡Bienvenida, <?php echo htmlspecialchars($_SESSION['nombre_clienta'] ?? 'Clienta'); ?>! 💅</h2>
        <p class="login-subtitle">Tu número de teléfono ha sido registrado con éxito.</p>
        
        <?php if (!empty($error_login)): ?>
            <div style="background-color: #fdf2f2; border: 1px solid #f8d7da; color: #a94442; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error_login); ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <a href="logout.php" class="btn-luxury" style="display: inline-block; text-decoration: none; background: #ef4444; color: white; padding: 10px 20px; border-radius: 6px;">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>