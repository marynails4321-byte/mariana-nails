<?php
session_start();
require_once 'conexion.php';

// Verificar que la clienta haya iniciado sesión
if (!isset($_SESSION['clienta_logged']) || $_SESSION['clienta_logged'] !== true) {
    header("Location: index.php");
    exit();
}

$clienta_id = $_SESSION['clienta_id'];
$nombre_clienta = $_SESSION['nombre_clienta'];
$error = '';
$exito = '';

// Procesar el formulario cuando la clienta selecciona su servicio y horario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio = trim($_POST['servicio'] ?? '');
    $foto_ejemplo = trim($_POST['foto_ejemplo'] ?? '');
    $fecha_seleccionada = trim($_POST['fecha'] ?? '');
    $hora_seleccionada = trim($_POST['hora'] ?? '');

    if (empty($servicio) || empty($fecha_seleccionada) || empty($hora_seleccionada)) {
        $error = "Por favor completa todos los campos y selecciona un horario.";
    } else {
        // Unir fecha y hora en un formato de timestamp completo
        $fecha_hora_cita = $fecha_seleccionada . ' ' . $hora_seleccionada . ':00';
        
        try {
            // REGLA DE NEGOCIO: Validar que no haya otra cita en un rango de menos de 3 horas (10800 segundos)
            $stmtCheck = $pdo->prepare("
                SELECT fecha_cita FROM citas 
                WHERE ABS(EXTRACT(EPOCH FROM (fecha_cita - TIMESTAMP :nueva_cita))) < 10800
            ");
            $stmtCheck->execute(['nueva_cita' => $fecha_hora_cita]);
            $cita_existente = $stmtCheck->fetch();

            if ($cita_existente) {
                $error = "Lo sentimos, este horario no está disponible. Debe haber un espacio mínimo de 3 horas entre cada turno.";
            } else {
                // Insertar la cita si el horario está libre
                $stmtInsert = $pdo->prepare("
                    INSERT INTO citas (clienta_id, servicio, foto_ejemplo, fecha_cita, estado) 
                    VALUES (:clienta_id, :servicio, :foto_ejemplo, :fecha_cita, 'Confirmada')
                ");
                $stmtInsert->execute([
                    'clienta_id' => $clienta_id,
                    'servicio' => $servicio,
                    'foto_ejemplo' => $foto_ejemplo,
                    'fecha_cita' => $fecha_hora_cita
                ]);
                
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
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="agendar.css">
</head>
<body class="login-body agenda-body-align">

    <div class="agenda-container" style="max-width: 600px;">
        
        <div class="agenda-header">
            <div>
                <h2 class="agenda-title">Reserva tu Turno ✨</h2>
                <p class="agenda-subtitle-text">Hola, <?php echo htmlspecialchars($nombre_clienta); ?></p>
            </div>
            <a href="clienta.php" class="logout-link">
                <i class="fa-solid fa-arrow-left"></i> Volver a mi panel
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="alert-success">
                <?php echo htmlspecialchars($exito); ?>
            </div>
        <?php endif; ?>

        <form action="agendar.php" method="POST">
            
            <!-- PASO 1: SELECCIONAR SERVICIO CON FOTO EN TARJETAS -->
            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 0.95rem;">1. Selecciona el Diseño o Servicio:</label>
            <div class="services-grid">
                
                <label class="service-card" onclick="selectService(this)">
                    <input type="radio" name="servicio" value="Uñas Acrílicas Elegantes" required>
                    <input type="hidden" name="foto_ejemplo" value="img/acrilicas.jpg">
                    <img src="img/acrilicas.jpg" alt="Acrílicas" onerror="this.src='https://via.placeholder.com/150?text=Acrilicas'">
                    <div class="service-title">Acrílicas Elegantes</div>
                </label>

                <label class="service-card" onclick="selectService(this)">
                    <input type="radio" name="servicio" value="Esmaltado Semipermanente">
                    <input type="hidden" name="foto_ejemplo" value="img/semi.jpg">
                    <img src="img/semi.jpg" alt="Semipermanente" onerror="this.src='https://via.placeholder.com/150?text=Semi'">
                    <div class="service-title">Semipermanente</div>
                </label>

                <label class="service-card" onclick="selectService(this)">
                    <input type="radio" name="servicio" value="Kapping Gel">
                    <input type="hidden" name="foto_ejemplo" value="img/kapping.jpg">
                    <img src="img/kapping.jpg" alt="Kapping" onerror="this.src='https://via.placeholder.com/150?text=Kapping'">
                    <div class="service-title">Kapping Gel</div>
                </label>

            </div>

            <!-- PASO 2: SELECCIONAR FECHA -->
            <div class="form-group-luxury" style="margin-bottom: 15px;">
                <label for="fecha">2. Fecha de tu cita</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="date" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 10px; border: none; outline: none; background: transparent;">
                </div>
            </div>

            <!-- PASO 3: SELECCIONAR HORA -->
            <div class="form-group-luxury" style="margin-bottom: 25px;">
                <label for="hora">3. Hora preferida</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-clock"></i>
                    <select id="hora" name="hora" required style="width: 100%; padding: 10px; border: none; outline: none; background: transparent;">
                        <option value="">Selecciona una hora...</option>
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
                        <option value="19:00">07:00 PM</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-luxury" style="width: 100%;">Confirmar y Reservar Cita</button>

        </form>
    </div>

    <script>
        // Función visual para resaltar la tarjeta de servicio seleccionada
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