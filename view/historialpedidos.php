<?php
require_once __DIR__ . '/../config/app.php';
session_start();
if (!isset($_SESSION['user_id_usuario'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';
$user_id_usuario = $_SESSION['user_id_usuario'];

$stmt = $pdo->prepare('SELECT id_pedido, fecha, estado FROM pedidos WHERE id_usuario = ? ORDER BY fecha DESC');
$stmt->execute([$user_id_usuario]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-6xl px-6 py-12">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Historial de Pedidos</h1>

        <?php if (count($pedidos) === 0): ?>
            <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8 text-center">
                <i class="bi bi-inbox text-6xl text-slate-300 mb-4 block"></i>
                <p class="text-lg text-slate-600">No tienes pedidos registrados.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden">
                        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">
                                        <i class="bi bi-receipt me-2"></i>Pedido #<?= htmlspecialchars($pedido['id_pedido']); ?>
                                    </h3>
                                    <p class="text-sm text-slate-600 mt-1">
                                        <i class="bi bi-calendar me-1"></i> <?= htmlspecialchars($pedido['fecha']); ?>
                                    </p>
                                </div>
                                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold bg-emerald-200 text-emerald-800">
                                    <?= htmlspecialchars($pedido['estado']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-6">
                            <?php
                            $stmtProd = $pdo->prepare('
                                SELECT p.nombre, dp.cantidad, dp.precio_unitario
                                FROM pedido_detalle dp
                                INNER JOIN productos p ON dp.id_producto = p.id_producto
                                WHERE dp.id_pedido = ?
                            ');
                            $stmtProd->execute([$pedido['id_pedido']]);
                            $productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

                            // Obtener el pago asociado al pedido
                            $stmtPago = $pdo->prepare('
                                SELECT id_pago, metodo, estado 
                                FROM pagos 
                                WHERE id_pedido = ? 
                                ORDER BY fecha_pago DESC 
                                LIMIT 1
                            ');
                            $stmtPago->execute([$pedido['id_pedido']]);
                            $pago = $stmtPago->fetch(PDO::FETCH_ASSOC);
                            ?>

                            <?php if (count($productos) === 0): ?>
                                <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-yellow-800 text-sm">
                                    <i class="bi bi-exclamation-triangle me-2"></i>No hay productos en este pedido.
                                </div>
                            <?php else: ?>
                                <div class="overflow-x-auto mb-6">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-100 border-b border-slate-200">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-semibold">Producto</th>
                                                <th class="px-4 py-3 text-center font-semibold">Cantidad</th>
                                                <th class="px-4 py-3 text-right font-semibold">Precio Unitario</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            <?php foreach ($productos as $prod): ?>
                                                <tr>
                                                    <td class="px-4 py-3"><?= htmlspecialchars($prod['nombre']); ?></td>
                                                    <td class="px-4 py-3 text-center"><?= htmlspecialchars($prod['cantidad']); ?></td>
                                                    <td class="px-4 py-3 text-right">$<?= number_format($prod['precio_unitario'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <div class="flex gap-3 flex-wrap">
                                <?php if ($pago && !empty($pago['id_pago'])): ?>
                                    <a href="../controller/generar_factura.php?id_pago=<?= $pago['id_pago']; ?>"
                                       target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-500 shadow-md">
                                        <i class="bi bi-file-earmark-pdf"></i> Factura PDF
                                    </a>
                                    <span class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
                                        <i class="bi bi-cash-coin me-1"></i> <?= ucfirst(htmlspecialchars($pago['metodo'])); ?>
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold <?= $pago['estado'] === 'completado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?= $pago['estado'] === 'completado' ? '✓ Pagado' : '⏳ Pendiente de efectivo'; ?>
                                    </span>
                                <?php else: ?>
                                    <button disabled class="inline-flex items-center gap-2 rounded-xl bg-slate-300 text-slate-600 px-4 py-2 text-sm font-semibold cursor-not-allowed">
                                        <i class="bi bi-file-earmark-pdf"></i> Sin factura
                                    </button>
                                <?php endif; ?>
                                
                                <form action="../controller/eliminar_pedido.php" method="POST" class="inline">
                                    <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido']; ?>">
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-600 text-white px-4 py-2 text-sm font-semibold hover:bg-red-700"
                                            onclick="return confirm('¿Seguro que deseas eliminar este pedido?');">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="perfil.php" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 text-slate-700 px-6 py-3 font-semibold hover:bg-slate-50">
                <i class="bi bi-arrow-left"></i> Volver al Perfil
            </a>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>
