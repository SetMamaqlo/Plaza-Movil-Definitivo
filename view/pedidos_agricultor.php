<?php
require_once __DIR__ . '/../config/app.php';
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/../config/session_timeout.php';

// Validar que sea agricultor
if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] != 3) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['user_id_usuario'];
$id_agricultor = $_SESSION['user_id_agricultor'] ?? null;

if (!$id_agricultor) {
    die("No se encontró agricultor asociado.");
}

// Obtener pedidos del agricultor
$stmt = $pdo->prepare("
    SELECT DISTINCT
        ped.id_pedido, ped.fecha, ped.estado,
        u.nombre_completo AS cliente, u.email AS cliente_email, u.telefono,
        SUM(pd.cantidad * pd.precio_unitario) AS total_pedido
    FROM pedido_detalle pd
    INNER JOIN productos p ON pd.id_producto = p.id_producto
    INNER JOIN agricultor a ON p.id_agricultor = a.id_agricultor
    INNER JOIN pedidos ped ON pd.id_pedido = ped.id_pedido
    INNER JOIN usuarios u ON ped.id_usuario = u.id_usuario
    WHERE a.id_agricultor = ?
    GROUP BY ped.id_pedido
    ORDER BY ped.fecha DESC
");
$stmt->execute([$id_agricultor]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Actualizar estado si lo envían
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $_POST['nuevo_estado'];
    
    // Validar estados permitidos
    if (!in_array($nuevo_estado, ['pendiente', 'aprobado', 'entregado'])) {
        die("Estado no válido");
    }
    
    try {
        $updateStmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $updateStmt->execute([$nuevo_estado, $id_pedido]);
        
        // Redirigir con éxito
        header("Location: pedidos_agricultor.php?success=1");
        exit;
    } catch (Exception $e) {
        error_log("Error al actualizar pedido: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Agricultor - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-7xl px-6 py-12">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Pedidos para Entrega</h1>
            <p class="text-slate-600">Gestiona los pedidos que tus clientes han realizado. Actualiza el estado de pendiente → aprobado → entregado</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                <i class="bi bi-check-circle me-2"></i> Estado del pedido actualizado correctamente.
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-12 text-center">
                <i class="bi bi-inbox text-6xl text-slate-300 mb-4 block"></i>
                <p class="text-lg text-slate-600 mb-6">No tienes pedidos pendientes por el momento.</p>
                <a href="mis_productos.php" class="inline-block rounded-xl bg-emerald-600 text-white font-semibold px-6 py-3 hover:bg-emerald-500">
                    <i class="bi bi-basket me-2"></i>Ver mis productos
                </a>
            </div>
        <?php else: ?>
            <!-- Resumen de estados -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="rounded-2xl bg-yellow-50 border border-yellow-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-yellow-700 mb-1">Pendientes</p>
                            <p class="text-3xl font-bold text-yellow-800">
                                <?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')); ?>
                            </p>
                        </div>
                        <i class="bi bi-hourglass-split text-4xl text-yellow-400"></i>
                    </div>
                </div>

                <div class="rounded-2xl bg-blue-50 border border-blue-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-700 mb-1">Aprobados</p>
                            <p class="text-3xl font-bold text-blue-800">
                                <?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'aprobado')); ?>
                            </p>
                        </div>
                        <i class="bi bi-check-circle text-4xl text-blue-400"></i>
                    </div>
                </div>

                <div class="rounded-2xl bg-green-50 border border-green-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-green-700 mb-1">Entregados</p>
                            <p class="text-3xl font-bold text-green-800">
                                <?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'entregado')); ?>
                            </p>
                        </div>
                        <i class="bi bi-box-seam text-4xl text-green-400"></i>
                    </div>
                </div>
            </div>

            <!-- Listado de pedidos -->
            <div class="space-y-6">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">
                                        <i class="bi bi-receipt me-2"></i>Pedido #<?= htmlspecialchars($pedido['id_pedido']); ?>
                                    </h3>
                                    <p class="text-sm text-slate-600 mt-1">
                                        <i class="bi bi-calendar me-1"></i> <?= htmlspecialchars($pedido['fecha']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-emerald-600">$<?= number_format($pedido['total_pedido'], 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <!-- Información del cliente -->
                            <div class="rounded-lg bg-slate-50 p-4 border border-slate-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700 mb-1">Cliente</p>
                                        <p class="text-slate-900"><?= htmlspecialchars($pedido['cliente']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700 mb-1">Email</p>
                                        <p class="text-slate-900">
                                            <a href="mailto:<?= htmlspecialchars($pedido['cliente_email']); ?>" class="text-emerald-600 hover:text-emerald-700">
                                                <?= htmlspecialchars($pedido['cliente_email']); ?>
                                            </a>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700 mb-1">Teléfono</p>
                                        <p class="text-slate-900">
                                            <a href="tel:<?= htmlspecialchars($pedido['telefono']); ?>" class="text-emerald-600 hover:text-emerald-700">
                                                <?= htmlspecialchars($pedido['telefono']); ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Productos del pedido -->
                            <div>
                                <p class="text-sm font-semibold text-slate-700 mb-3">Productos en este pedido:</p>
                                <div class="space-y-2">
                                    <?php
                                    $prodStmt = $pdo->prepare("
                                        SELECT p.nombre, pd.cantidad, pd.precio_unitario
                                        FROM pedido_detalle pd
                                        INNER JOIN productos p ON pd.id_producto = p.id_producto
                                        WHERE pd.id_pedido = ? AND p.id_agricultor = ?
                                    ");
                                    $prodStmt->execute([$pedido['id_pedido'], $id_agricultor]);
                                    $productos = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($productos as $prod):
                                    ?>
                                    <div class="flex items-center justify-between text-sm bg-slate-50 rounded-lg p-3">
                                        <div>
                                            <p class="font-semibold text-slate-900"><?= htmlspecialchars($prod['nombre']); ?></p>
                                            <p class="text-slate-600">Cantidad: <?= $prod['cantidad']; ?> × $<?= number_format($prod['precio_unitario'], 2); ?></p>
                                        </div>
                                        <p class="font-bold text-emerald-600">$<?= number_format($prod['cantidad'] * $prod['precio_unitario'], 2); ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Estado y acciones -->
                            <div class="border-t border-slate-200 pt-4">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700 mb-2">Estado actual:</p>
                                        <?php
                                        $estadoClases = [
                                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                                            'aprobado' => 'bg-blue-100 text-blue-800',
                                            'entregado' => 'bg-green-100 text-green-800'
                                        ];
                                        $estadoIconos = [
                                            'pendiente' => 'hourglass-split',
                                            'aprobado' => 'check-circle',
                                            'entregado' => 'box-seam'
                                        ];
                                        $clase = $estadoClases[$pedido['estado']] ?? 'bg-slate-100 text-slate-800';
                                        $icono = $estadoIconos[$pedido['estado']] ?? 'question-circle';
                                        ?>
                                        <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold <?= $clase; ?>">
                                            <i class="bi bi-<?= $icono; ?> me-1"></i><?= ucfirst($pedido['estado']); ?>
                                        </span>
                                    </div>

                                    <!-- Botones para cambiar estado -->
                                    <?php if ($pedido['estado'] === 'pendiente'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido']; ?>">
                                            <input type="hidden" name="nuevo_estado" value="aprobado">
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 text-sm font-semibold hover:bg-blue-700">
                                                <i class="bi bi-check-circle me-1"></i> Aprobar Pedido
                                            </button>
                                        </form>
                                    <?php elseif ($pedido['estado'] === 'aprobado'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido']; ?>">
                                            <input type="hidden" name="nuevo_estado" value="entregado">
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-green-600 text-white px-4 py-2 text-sm font-semibold hover:bg-green-700">
                                                <i class="bi bi-box-seam me-1"></i> Marcar Entregado
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-2 rounded-xl bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                            <i class="bi bi-check-all me-1"></i> Completado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>
