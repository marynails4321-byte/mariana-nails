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

if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: admin.php");
    exit();
}

// ==========================================
// GESTIÓN DE META FINANCIERA
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_meta') {
    $nueva_meta = floatval($_POST['meta_monto'] ?? 0);
    $_SESSION['meta_financiera'] = $nueva_meta;
    header("Location: finanzas.php?mes=" . ($_POST['mes_actual'] ?? date('Y-m')));
    exit();
}

$meta_financiera = $_SESSION['meta_financiera'] ?? 50000;

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
            $mes_redireccion = date('Y-m', strtotime($fecha));
            header("Location: finanzas.php?mes=" . $mes_redireccion);
            exit();
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
    $mes_actual_get = $_GET['mes'] ?? date('Y-m');
    try {
        $stmtDelFinanza = $pdo->prepare("DELETE FROM finanzas WHERE id = :id");
        $stmtDelFinanza->execute(['id' => $id_finanza]);
        header("Location: finanzas.php?mes=" . $mes_actual_get);
        exit();
    } catch (PDOException $e) {
        $error_db = "Error al eliminar el movimiento.";
    }
}

// ==========================================
// FILTRO DE MES SELECCIONADO
// ==========================================
$mesSeleccionado = $_GET['mes'] ?? date('Y-m');

// ==========================================
// OBTENER DATOS Y CALCULAR TOTALES POR MES
// ==========================================
$totalIngresos = 0;
$totalGastos = 0;
$ganancia_total = 0;
$movimientos = [];

try {
    // Uso de CAST(fecha AS TEXT) para compatibilidad con bases de datos estrictas como PostgreSQL
    $stmtFinanzas = $pdo->prepare("SELECT * FROM finanzas WHERE CAST(fecha AS TEXT) LIKE :mes ORDER BY fecha DESC, id DESC");
    $stmtFinanzas->execute(['mes' => $mesSeleccionado . '%']);
    $movimientos = $stmtFinanzas->fetchAll(PDO::FETCH_ASSOC);

    foreach ($movimientos as $m) {
        if ($m['tipo'] === 'ingreso') {
            $totalIngresos += floatval($m['monto']);
        } else {
            $totalGastos += floatval($m['monto']);
        }
    }
    $ganancia_total = $totalIngresos - $totalGastos;

} catch (PDOException $e) {
    $error_db = "Error al cargar la base de datos: " . $e->getMessage();
}

$porcentaje_meta = ($meta_financiera > 0) ? min(round(($ganancia_total / $meta_financiera) * 100, 1), 100) : 0;
$meta_cumplida = $ganancia_total >= $meta_financiera;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Financiero - Mariana Nails Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="finanzas.css?v=<?php echo time(); ?>">
    <style>
        .filtro-mes-container {
            background: #fff; border: 1px solid var(--luxury-border); padding: 15px 20px; border-radius: 10px;
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;
        }
        .meta-box {
            background: #faf7f2; border: 1px solid #e6dfd5; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px;
        }
        .progress-bar-container {
            background: #e5e7eb; border-radius: 10px; height: 12px; width: 100%; overflow: hidden; margin-top: 8px; position: relative;
        }
        .progress-bar-fill {
            background: linear-gradient(90deg, #d97706, #10b981); height: 100%; width: 0%; transition: width 0.5s ease;
        }
    </style>
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

        <!-- SELECTOR DE MES -->
        <div class="filtro-mes-container">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-calendar-alt" style="color: #d97706; font-size: 1.2rem;"></i>
                <span style="font-weight: 600; color: #374151;">Seleccionar Mes a Visualizar:</span>
            </div>
            <form method="GET" action="finanzas.php" style="display: flex; gap: 10px; align-items: center;">
                <input type="month" name="mes" value="<?php echo htmlspecialchars($mesSeleccionado); ?>" style="padding: 7px 12px; border: 1px solid var(--luxury-border); border-radius: 6px; font-size: 0.9rem;" required>
                <button type="submit" class="btn-luxury" style="padding: 8px 15px; font-size: 0.85rem;">Filtrar</button>
            </form>
        </div>

        <!-- TARJETAS DE RESUMEN SUPERIOR -->
        <div class="finanzas-cards-grid">
            <div class="finanza-card card-ingresos">
                <div class="finanza-card-title"><i class="fa-solid fa-arrow-trend-up" style="color: #10b981;"></i> Ingresos del Mes</div>
                <div class="finanza-card-amount" style="color: #047857;">$<?php echo number_format($totalIngresos, 2); ?></div>
            </div>
            <div class="finanza-card card-gastos">
                <div class="finanza-card-title"><i class="fa-solid fa-arrow-trend-down" style="color: #ef4444;"></i> Gastos del Mes</div>
                <div class="finanza-card-amount" style="color: #b91c1c;">$<?php echo number_format($totalGastos, 2); ?></div>
            </div>
            <div class="finanza-card card-ganancia">
                <div class="finanza-card-title"><i class="fa-solid fa-wallet" style="color: #d97706;"></i> Ganancia Neta</div>
                <div class="finanza-card-amount" style="color: #b45309;">$<?php echo number_format($ganancia_total, 2); ?></div>
            </div>
        </div>

        <!-- SECCIÓN DE META FINANCIERA MENSUAL -->
        <div class="meta-box">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                <div>
                    <h4 style="margin: 0; font-size: 1rem; color: #374151;">
                        <i class="fa-solid fa-bullseye" style="color: #d97706;"></i> Meta de Ganancia Mensual: 
                        <strong style="color: #b45309;">$<?php echo number_format($meta_financiera, 2); ?></strong>
                    </h4>
                    <p style="margin: 3px 0 0 0; font-size: 0.82rem; color: var(--luxury-muted);">
                        <?php if ($meta_cumplida): ?>
                            <span style="color: #047857; font-weight: 600;"><i class="fa-solid fa-check-circle"></i> ¡Meta cumplida o superada este mes! 🥳</span>
                        <?php else: ?>
                            <span>Te faltan <strong>$<?php echo number_format($meta_financiera - $ganancia_total, 2); ?></strong> para alcanzar la meta.</span>
                        <?php endif; ?>
                    </p>
                </div>
                <form method="POST" action="finanzas.php" style="display: flex; gap: 5px; align-items: center;">
                    <input type="hidden" name="accion" value="guardar_meta">
                    <input type="hidden" name="mes_actual" value="<?php echo htmlspecialchars($mesSeleccionado); ?>">
                    <input type="number" step="01" name="meta_monto" placeholder="Nueva meta" value="<?php echo $meta_financiera; ?>" style="width: 110px; padding: 5px 8px; border: 1px solid var(--luxury-border); border-radius: 5px; font-size: 0.85rem;" required>
                    <button type="submit" style="background: #374151; color: #fff; border: none; padding: 6px 10px; border-radius: 5px; font-size: 0.8rem; cursor: pointer;">Actualizar Meta</button>
                </form>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--luxury-muted);">
                <span>Progreso: <?php echo $porcentaje_meta; ?>%</span>
                <span>100% (Meta)</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $porcentaje_meta; ?>%;"></div>
            </div>
        </div>

        <!-- FORMULARIO VERTICAL PARA NUEVO MOVIMIENTO -->
        <div class="finanza-form-container">
            <h4 class="finanza-form-title">
                <i class="fa-solid fa-circle-plus" style="color: #d97706;"></i> Registrar Nuevo Movimiento
            </h4>
            
            <form action="finanzas.php" method="POST" class="finanza-form">
                <input type="hidden" name="accion" value="registrar_finanza">
                
                <div class="form-row-grid">
                    <div class="finanza-input-group">
                        <label>Tipo de Movimiento</label>
                        <select name="tipo_movimiento" required>
                            <option value="ingreso">🟢 Ingreso</option>
                            <option value="gasto" selected>🔴 Gasto</option>
                        </select>
                    </div>

                    <div class="finanza-input-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha_movimiento" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="form-row-grid-wide">
                    <div class="finanza-input-group">
                        <label>Concepto / Descripción</label>
                        <input type="text" name="concepto" placeholder="Ej. Compra de acrílico o Servicio de Uñas" required>
                    </div>

                    <div class="finanza-input-group">
                        <label>Monto ($)</label>
                        <input type="number" step="0.01" name="monto" placeholder="Ej. 15000" required>
                    </div>
                </div>

                <div style="margin-top: 5px;">
                    <button type="submit" class="btn-luxury btn-guardar-finanza">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Movimiento Financiero
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
                            <td colspan="5" style="text-align: center; color: var(--luxury-muted); padding: 20px;">No hay movimientos registrados en el mes de <strong><?php echo htmlspecialchars($mesSeleccionado); ?></strong>.</td>
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
                                    <a href="finanzas.php?eliminar_finanza=<?php echo $m['id']; ?>&mes=<?php echo $mesSeleccionado; ?>" onclick="return confirm('¿Estás segura de eliminar este registro?');" style="color: #ef4444; text-decoration: none; font-size: 0.85rem;" title="Eliminar">
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