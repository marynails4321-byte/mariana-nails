<?php
session_start();
require_once 'conexion.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_servicio') {
    $id_servicio = intval($_POST['id']);
    $nuevo_precio = floatval($_POST['precio']);
    $ruta_foto = trim($_POST['foto_actual']); // Mantenemos la foto actual por defecto

    $file_input_name = 'imagen_' . $id_servicio;

    // Verificar si se seleccionó un nuevo archivo de imagen
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES[$file_input_name]['tmp_name'];
        $file_type = mime_content_type($file_tmp);

        // Validar que realmente sea una imagen
        if (strpos($file_type, 'image/') === 0) {
            // Leer el contenido binario del archivo y convertirlo a Base64 para guardarlo en la BD
            $file_data = file_get_contents($file_tmp);
            $base64_image = 'data:' . $file_type . ';base64,' . base64_encode($file_data);
            $ruta_foto = $base64_image;
        } else {
            $error = "Por favor selecciona un archivo de imagen válido.";
        }
    } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "Hubo un error al procesar el archivo seleccionado.";
    }

    if (empty($error)) {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE servicios 
                SET precio = :precio, foto = :foto 
                WHERE id = :id
            ");
            $stmtUpdate->execute(array(
                'precio' => $nuevo_precio,
                'foto' => $ruta_foto,
                'id' => $id_servicio
            ));
            $exito = "¡El servicio ha sido actualizado correctamente!";
        } catch (PDOException $e) {
            $error = "Error al actualizar en la base de datos: " . $e->getMessage();
        }
    }
}

try {
    $stmtServicios = $pdo->query("SELECT * FROM servicios ORDER BY id ASC");
    $servicios_db = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $servicios_db = [];
    $error = "Error al cargar los servicios de la base de datos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Servicios - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; background-color: #f7f4ed;">
    
    <div class="agenda-container" style="max-width: 900px; width: 100%; background: #fff; border: 1px solid #e2d9cc; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
        
        <div class="agenda-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2d9cc; padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 class="agenda-title" style="font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; margin: 0; color: #2c2c2c;">Modificar Precios e Imágenes ✨</h2>
                <p class="agenda-subtitle-text" style="margin: 5px 0 0 0; color: #8c8275; font-size: 0.85rem;">Panel de Administración de Servicios</p>
            </div>
            <a href="admin.php" class="logout-link" style="background: #fdf2f2; color: #9c3c3c; border: 1px solid #f8d7da; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 500;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error" style="background-color: #fdf2f2; border: 1px solid #f8d7da; color: #9c3c3c; padding: 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="alert-success" style="background-color: #f2f9f5; border: 1px solid #d1e7dd; color: #0f5132; padding: 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px;">
                <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($exito); ?>
            </div>
        <?php endif; ?>

        <div class="services-admin-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <?php if (empty($servicios_db)): ?>
                <p style="color: #8c8275; grid-column: 1 / -1; text-align: center;">No hay servicios registrados.</p>
            <?php else: ?>
                <?php foreach ($servicios_db as $serv): ?>
                    <form action="admin_servicios.php" method="POST" enctype="multipart/form-data" class="service-admin-item" style="background: #faf8f5; border: 1px solid #e2d9cc; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; gap: 12px;">
                        <input type="hidden" name="action" value="actualizar_servicio">
                        <input type="hidden" name="id" value="<?php echo $serv['id']; ?>">
                        <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($serv['foto']); ?>">

                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="<?php echo htmlspecialchars($serv['foto']); ?>" alt="Servicio" style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid #e2d9cc;" onerror="this.src='https://via.placeholder.com/150?text=Error'">
                            <div>
                                <span style="font-size: 0.9rem; font-weight: 600; color: #2c2c2c; display: block;"><?php echo htmlspecialchars($serv['nombre']); ?></span>
                                <span style="font-size: 0.75rem; color: #8c8275;">ID: #<?php echo $serv['id']; ?></span>
                            </div>
                        </div>

                        <!-- Modificar Precio -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label style="font-size: 0.75rem; font-weight: 500; color: #8c8275;">Precio ($):</label>
                            <input type="number" step="0.01" name="precio" value="<?php echo $serv['precio']; ?>" required style="width: 100%; padding: 8px; border: 1px solid #e2d9cc; border-radius: 6px; background: #fff; box-sizing: border-box;">
                        </div>

                        <!-- Selector de imagen desde la PC -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label style="font-size: 0.75rem; font-weight: 500; color: #8c8275;">Cambiar Imagen (Opcional):</label>
                            <input type="file" name="imagen_<?php echo $serv['id']; ?>" accept="image/*" style="width: 100%; padding: 6px; border: 1px solid #e2d9cc; border-radius: 6px; background: #fff; box-sizing: border-box; font-size: 0.8rem;">
                        </div>

                        <button type="submit" class="btn-luxury" style="background: linear-gradient(135deg, #d4af37 0%, #b89728 100%); color: white; border: none; border-radius: 6px; padding: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; margin-top: 5px;">
                            Guardar Cambios
                        </button>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>