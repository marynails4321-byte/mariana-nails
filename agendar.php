<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['clienta_logged']) || $_SESSION['clienta_logged'] !== true) {
    header("Location: index.php");
    exit();
}

$clienta_id = $_SESSION['clienta_id'];
$nombre_clienta = $_SESSION['nombre_clienta'];
$error = '';
$exito = '';

try {
    $stmtServicios = $pdo->query("SELECT * FROM servicios ORDER BY id ASC");
    $servicios_db = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $servicios_db = [];
    $error = "Error al cargar los servicios disponibles.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio = isset($_POST['servicio']) ? trim($_POST['servicio']) : '';
    $foto_ejemplo = isset($_POST['foto_ejemplo']) ? trim($_POST['foto_ejemplo']) : 'img/default.jpg';
    $fecha_seleccionada = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
    $hora_seleccionada = isset($_POST['hora']) ? trim($_POST['hora']) : '';

    if (empty($servicio) || empty($fecha_seleccionada) || empty($hora_seleccionada)) {
        $error = "Por favor completa todos los campos y selecciona un horario.";
    } else {
        $fecha_hora_cita = $fecha_seleccionada . ' ' . $hora_seleccionada . ':00';
        
       try {
            // CORREGIDO: Se agregó AND estado != 'Rechazada' para liberar el horario si la admin lo rechazó
            $stmtCheck = $pdo->prepare("
                SELECT fecha_cita FROM citas 
                WHERE estado != 'Rechazada' 
                AND ABS(EXTRACT(EPOCH FROM (fecha_cita - :nueva_cita::timestamp))) < 10800
            ");
            $stmtCheck->execute(array('nueva_cita' => $fecha_hora_cita));
            $cita_existente = $stmtCheck->fetch();

            if ($cita_existente) {
                $error = "Gracias por tu interes pero esta hora ya se encuentra agendada. Lo sentimos, debe haber un espacio mínimo de 3 horas entre cada turno.";
            } else {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO citas (clienta_id, servicio, foto_ejemplo, fecha_cita, estado) 
                    VALUES (:clienta_id, :servicio, :foto_ejemplo, :fecha_cita, 'Pendiente')
                ");
                $stmtInsert->execute(array(
                    'clienta_id' => $clienta_id,
                    'servicio' => $servicio,
                    'foto_ejemplo' => $foto_ejemplo,
                    'fecha_cita' => $fecha_hora_cita
                ));
                
                $exito = "¡Tu cita ha sido agendada con éxito!";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema al procesar la cita: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="agendar.css">
</head>
<body class="login-body" style="display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px;">
    <div class="agenda-container">
        
        <div class="agenda-header">
            <div>
                <h2 class="agenda-title">Reserva tu Turno ✨</h2>
                <p class="agenda-subtitle-text">Hola, <?php echo htmlspecialchars($nombre_clienta); ?></p>
            </div>
            <a href="clienta.php" class="logout-link">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($exito); ?>
            </div>
        <?php endif; ?>

        <form action="agendar.php" method="POST">
            
            <label class="form-label-luxury">1. Selecciona el servicio que deseas:</label>
            <div class="services-grid">
                
                <?php if (empty($servicios_db)): ?>
                    <p style="color: var(--luxury-muted); grid-column: 1 / -1; text-align: center;">No hay servicios disponibles en este momento.</p>
                <?php else: ?>
                    <?php foreach ($servicios_db as $serv): ?>
                        <label class="service-card" onclick="selectService(this)">
                            <input type="radio" name="servicio" value="<?php echo htmlspecialchars($serv['nombre']); ?>" required>
                            <input type="hidden" name="foto_ejemplo" value="<?php echo htmlspecialchars($serv['foto']); ?>">
                            <img src="<?php echo htmlspecialchars($serv['foto']); ?>" alt="<?php echo htmlspecialchars($serv['nombre']); ?>" onerror="this.src='https://via.placeholder.com/150?text=Nails'">
                            <div class="service-title"><?php echo htmlspecialchars($serv['nombre']); ?></div>
                            <div class="service-info">Precio: $<?php echo number_format($serv['precio'], 0, ',', '.'); ?></div>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

            <div class="form-group-luxury">
                <label for="fecha">2. Fecha de tu cita</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="date" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="form-group-luxury" style="margin-bottom: 25px;">
                <label for="hora">3. Hora preferida (Intervalos de 3 horas mínimas)</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-clock"></i>
                    <select id="hora" name="hora" required>
                        <option value="">Selecciona una hora...</option>
                        <option value="08:00">08:00 AM</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="13:00">01:00 PM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="17:00">05:00 PM</option>
                        <option value="18:00">06:00 PM</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-luxury">Confirmar y Reservar Cita</button>

        </form>
    </div>

    <script>
        function selectService(cardElement) {
            document.querySelectorAll('.service-card').forEach(card => {
                card.classList.remove('selected');
            });
            cardElement.classList.add('selected');
            cardElement.querySelector('input[type="radio"]').checked = true;
        }
    </script>
</body>
</html>