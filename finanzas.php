<?php
session_start();
require_once 'conexion.php';

$error_db = '';
$mensaje_exito = '';

// ==========================================
// VERIFICAR COOKIE PERSISTENTE DE ADMINISTRADOR
// ==========================================
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    if (isset($_COOKIE['cookie_admin_logged']) && $_COOKIE['cookie_admin_logged'] === 'true') {
        $_SESSION['admin_logged'] = true;
    }
}

// Si no ha iniciado sesión, redirigir al login principal
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: admin.php");
    exit();
}

// ==========================================
// PROCESAR NUEVO MOVIMIENTO FINANCIERO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_finanza') {
    $tipo = trim($_POST['tipo_movimiento'] ?? '');
    $concepto = trim($_POST['concepto'] ?? '');
    $monto = floatval($_POST['monto'] ?? 0);
    $fecha = trim($_POST['fecha_movimiento'] ?? date('Y-m-d'));

    if (!empty($tipo) && !empty($concepto) && $monto > 0 && !empty($fecha)) {
        try {
            $stmtFinanza = $pdo->prepare("INSERT INTO finanzas (tipo, concepto, monto, fecha) VALUES (:tipo, :concepto, :monto, :fecha)");
            $stmtFinanza->execute([
                'tipo' => $tipo,
                'concepto' => $concepto,
                'monto' => $monto,
                'fecha' => $fecha
            ]);
            $mensaje_exito = "¡Movimiento financiero registrado con éxito!";
        } catch (PDOException $e) {
            $error_db = "Error al registrar movimiento: " . $e->getMessage();
        }
    } else {
        $error_db = "Por favor completa todos los campos correctamente.";
    }
}

// ==========================================
// ELIMINAR MOVIMIENTO FINANCIERO
// ==========================================
if (isset($_GET['eliminar_finanza'])) {
    $id_finanza = intval($_GET['eliminar_finanza']);
    try {
        $stmtDelFinanza = $pdo->prepare("DELETE FROM finanzas WHERE id = :id");
        $stmtDelFinanza->execute(['id' => $id_finanza]);
        header("Location: finanzas.php");
        exit();
    } catch (PDOException $e) {
        $error_db = "Error al eliminar el movimiento.";
    }
}

// ==========================================
// OBTENER DATOS Y CALCULAR TOTALES
// ==========================================
try {
    $stmtFinanzas = $pdo->query("SELECT * FROM finanzas ORDER BY fecha DESC, id DESC");
    $movimientos = $stmtFinanzas->fetchAll(PDO::FETCH_ASSOC);

    $totalIngresos = 0;
    $totalGastos = 0;
    foreach ($movimientos as $m) {
        if ($m['tipo'] === 'ingreso') {
            $totalIngresos += $m['monto'];
        } else {
            $totalGastos += $m['monto'];
        }
    }
    $gananciaTotal = $totalIngresos - $totalGastos;

} catch (PDOException $e) {
    $error_db = "Error al cargar la base de datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Financiero - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tus estilos generales del panel -->
    <link rel="stylesheet" href="admin.css">
    <!-- Estilos específicos de finanzas separados -->
    <link rel="stylesheet" href="finanzas.css">
</head>
<body class="login-body agenda-body-align">

    <div class="agenda-container" style="max-width: 1000px; width: 100%;">
        
        <div class="agenda-header">
            <div>
                <h2 class="agenda-title">Monitoreo Financiero 👑</h2>
                <p class="agenda-subtitle-text">Mariana Nails Studio - Control de Ingresos y Gastos</p>
            </div>
            <a href="logout.php" class="logout-link" style="padding: 8px 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; color: #ef4444; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Salir
            </a>
        </div>

        <!-- MENÚ DE NAVEGACIÓN ENTRE PANELES -->
        <div class="nav-admin-menu">
            <a href="admin.php" class="nav-btn"><i class="fa-solid fa-calendar-days"></i> Agenda y Clientas</a>
            <a href="Admin_servicios.php" class="nav-btn"><i class="fa-solid fa-sliders"></i> Gestionar Servicios</a>
            <a href="finanzas.php" class="nav-btn active"><i class="fa-solid fa-wallet"></i> Control Financiero</a>
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

        <!-- TARJETAS DE RESUMEN SUPERIOR -->
        <div class="finanzas-cards-grid">
            <div class="finanza-card card-ingresos">
                <div class="finanza-card-title"><i class="fa-solid fa-arrow-trend-up" style="color: #10b981;"></i> Total Ingresos</div>
                <div class="finanza-card-amount" style="color: #047857;">$<?php echo number_format($totalIngresos, 2); ?></div>
            </div>
            <div class="finanza-card card-gastos">
                <div class="finanza-card-title"><i class="fa-solid fa-arrow-trend-down" style="color: #ef4444;"></i> Total Gastos</div>
                <div class="finanza-card-amount" style="color: #b91c1c;">$<?php echo number_format($totalGastos, 2); ?></div>
            </div>
            <div class="finanza-card card-ganancia">
                <div class="finanza-card-title"><i class="fa-solid fa-wallet" style="color: #d97706;"></i> Ganancia Neta Total</div>
                <div class="finanza-card-amount" style="color: #b45309;">$<?php echo number_format($gananciaTotal, 2); ?></div>
            </div>
        </div>

     <!-- FORMULARIO RÁPIDO PARA REGISTRAR -->
        <div class="finanza-form-container">
            <h4 style="margin-top: 0; margin-bottom: 18px; font-size: 1.05rem; color: #374151; font-weight: 600;">
                <i class="fa-solid fa-circle-plus" style="color: #d97706;"></i> Registrar Nuevo Movimiento
            </h4>
            <form action="finanzas.php" method="POST" class="finanza-form-grid">
                <input type="hidden" name="accion" value="registrar_finanza">
                
                <div class="finanza-input-group">
                    <label>Tipo</label>
                    <select name="tipo_movimiento" required>
                        <option value="ingreso">🟢 Ingreso</option>
                        <option value="gasto" selected>🔴 Gasto</option>
                    </select>
                </div>

                <div class="finanza-input-group">
                    <label>Concepto / Descripción</label>
                    <input type="text" name="concepto" placeholder="Ej. Compra de acrílico" required>
                </div>

                <div class="finanza-input-group">
                    <label>Monto ($)</label>
                    <input type="number" step="0.01" name="monto" placeholder="Ej. 15000" required>
                </div>

                <div class="finanza-input-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha_movimiento" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div>
                    <button type="submit" class="btn-luxury btn-guardar-finanza">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- TABLA DE HISTORIAL -->
        <div class="table-responsive">
            <table class="luxury-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--luxury-muted); padding: 20px;">No hay movimientos financieros registrados todavía.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['fecha']); ?></td>
                                <td>
                                    <?php if ($m['tipo'] === 'ingreso'): ?>
                                        <span class="badge-ingreso">Ingreso</span>
                                    <?php else: ?>
                                        <span class="badge-gasto">Gasto</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($m['concepto']); ?></strong></td>
                                <td style="font-weight: 600; color: <?php echo ($m['tipo'] === 'ingreso') ? '#047857' : '#b91c1c'; ?>;">
                                    <?php echo ($m['tipo'] === 'ingreso' ? '+' : '-'); ?>$<?php echo number_format($m['monto'], 2); ?>
                                </td>
                                <td>
                                    <a href="finanzas.php?eliminar_finanza=<?php echo $m['id']; ?>" onclick="return confirm('¿Estás segura de eliminar este registro?');" style="color: #ef4444; text-decoration: none; font-size: 0.85rem;" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>