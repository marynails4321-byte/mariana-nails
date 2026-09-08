<?php
session_start();
require_once 'conexion.php';

// Verificar que el administrador haya iniciado sesión
// (Ajusta la variable de sesión según cómo la tengas en tu login de admin, ej: $_SESSION['admin_logged'])
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: index.php");
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// ==========================================
// PROCESAR FORMULARIO DE ACTUALIZACIÓN DE PRECIOS E IMÁGENES
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_servicios'])) {
    try {
        // 1. Actualizar precios y fotos de cada servicio
        if (isset($_POST['precio_servicio']) && is_array($_POST['precio_servicio'])) {
            foreach ($_POST['precio_servicio'] as $id_servicio => $nuevo_precio) {
                
                $id_servicio = intval($id_servicio);
                $nuevo_precio = floatval($nuevo_precio);
                $ruta_foto_sql = "";

                // Verificar si se subió una nueva foto para este servicio específico
                if (isset($_FILES['foto_servicio']['name'][$id_servicio]) && $_FILES['foto_servicio']['error'][$id_servicio] === UPLOAD_ERR_OK) {
                    $archivo_tmp = $_FILES['foto_servicio']['tmp_name'][$id_servicio];
                    $nombre_original = basename($_FILES['foto_servicio']['name'][$id_servicio]);
                    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                    
                    // Validar extensiones permitidas
                    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
                    if (in_array($extension, $extensiones_permitidas)) {
                        $nuevo_nombre_foto = 'servicio_' . $id_servicio . '_' . time() . '.' . $extension;
                        $carpeta_destino = 'img/';
                        
                        // Crear la carpeta img si no existe
                        if (!is_dir($carpeta_destino)) {
                            mkdir($carpeta_destino, 0755, true);
                        }
                        
                        $ruta_completa = $carpeta_destino . $nuevo_nombre_foto;
                        if (move_uploaded_file($archivo_tmp, $ruta_completa)) {
                            $ruta_foto_sql = $ruta_completa;
                        }
                    }
                }

                // Actualizar en la base de datos (con o sin nueva foto)
                if ($ruta_foto_sql !== "") {
                    $stmtServ = $pdo->prepare("UPDATE servicios SET precio = :precio, foto = :foto WHERE id = :id");
                    $stmtServ->execute([
                        'precio' => $nuevo_precio,
                        'foto' => $ruta_foto_sql,
                        'id' => $id_servicio
                    ]);
                } else {
                    $stmtServ = $pdo->prepare("UPDATE servicios SET precio = :precio WHERE id = :id");
                    $stmtServ->execute([
                        'precio' => $nuevo_precio,
                        'id' => $id_servicio
                    ]);
                }
            }
        }

        // 2. Actualizar costo global de uñas malas
        if (isset($_POST['costo_unas_malas'])) {
            $costo_malas = floatval($_POST['costo_unas_malas']);
            $stmtGlobal = $pdo->prepare("UPDATE configuracion_global SET valor = :valor WHERE clave = 'costo_unas_malas'");
            $stmtGlobal->execute(['valor' => $costo_malas]);
        }

        $mensaje_exito = "¡Los servicios, precios e imágenes se han actualizado con éxito!";
    } catch (PDOException $e) {
        $mensaje_error = "Error al actualizar los datos en la base de datos: " . $e->getMessage();
    }
}

// Obtener la lista actual de servicios desde la base de datos
$stmtServicios = $pdo->query("SELECT * FROM servicios ORDER BY id ASC");
$servicios_db = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

// Obtener el costo actual de uñas malas
$stmtGlobal = $pdo->query("SELECT valor FROM configuracion_global WHERE clave = 'costo_unas_malas'");
$costo_malas_actual = $stmtGlobal->fetchColumn();
if ($costo_malas_actual === false) {
    $costo_malas_actual = 10000.00;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="admin.css">
  
</head>
<body>

    <div class="admin-container">
        
        <!-- Cabecera del Panel -->
        <div class="admin-header">
            <div>
                <h1 class="admin-title">Panel de Administración ✨</h1>
                <p style="margin: 5px 0 0 0; color: var(--luxury-muted); font-size: 0.9rem;">Mariana Nails Studio • Control General</p>
            </div>
            <a href="logout.php" class="logout-link logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>

        <!-- SECCIÓN DE GESTIÓN DE SERVICIOS, PRECIOS E IMÁGENES -->
        <div class="admin-card">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; margin-top: 0; margin-bottom: 10px;">
                Gestión de Servicios, Precios e Imágenes ✨
            </h3>
            <p style="color: var(--luxury-muted); font-size: 0.85rem; margin-bottom: 20px;">
                Modifica los precios o sube fotos nuevas para los servicios. Los cambios se reflejarán instantáneamente cuando las clientas vayan a agendar.
            </p>

            <?php if (!empty($mensaje_exito)): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($mensaje_exito); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensaje_error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <!-- IMPORTANTE: enctype="multipart/form-data" es obligatorio para poder enviar las imágenes -->
            <form action="admin.php" method="POST" enctype="multipart/form-data">
                
                <p style="font-size: 0.9rem; font-weight: 600; margin-bottom: 15px;">Listado de Servicios Activos:</p>

                <div class="services-admin-grid">
                    <?php foreach ($servicios_db as $serv): ?>
                        <div class="service-admin-item">
                            
                            <div class="service-info-row">
                                <img src="<?php echo htmlspecialchars($serv['foto']); ?>" alt="Foto" class="service-thumb" onerror="this.src='https://via.placeholder.com/50?text=Nails'">
                                <div>
                                    <strong style="font-size: 0.85rem; display: block; color: var(--luxury-text);"><?php echo htmlspecialchars($serv['nombre']); ?></strong>
                                    <span style="font-size: 0.75rem; color: var(--luxury-muted);">ID Servicio: #<?php echo $serv['id']; ?></span>
                                </div>
                            </div>

                            <!-- Input para el Precio -->
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 500; color: var(--luxury-muted); display: block; margin-bottom: 3px;">Precio ($):</label>
                                <input type="number" step="100" name="precio_servicio[<?php echo $serv['id']; ?>]" value="<?php echo $serv['precio']; ?>" required>
                            </div>

                            <!-- Input para cambiar la Foto -->
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 500; color: var(--luxury-muted); display: block; margin-bottom: 3px;">Cambiar Imagen (Opcional):</label>
                                <input type="file" name="foto_servicio[<?php echo $serv['id']; ?>]" accept="image/*">
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Configuración del recargo global por uñas malas -->
                <div style="max-width: 320px; background: #faf8f5; border: 1px solid var(--luxury-border); border-radius: 12px; padding: 15px; margin-bottom: 25px;">
                    <label style="font-size: 0.85rem; font-weight: 600; display: block; margin-bottom: 6px; color: var(--luxury-text);">
                        Recargo por Uñas Malas ($)
                    </label>
                    <p style="font-size: 0.75rem; color: var(--luxury-muted); margin: 0 0 8px 0;">Costo extra que se le suma si la clienta marca la casilla correspondiente.</p>
                    <input type="number" step="100" name="costo_unas_malas" value="<?php echo $costo_malas_actual; ?>" required>
                </div>

                <button type="submit" name="actualizar_servicios" class="btn-luxury">
                    Guardar Todos los Cambios
                </button>

            </form>
        </div>

    </div>

</body>
</html>