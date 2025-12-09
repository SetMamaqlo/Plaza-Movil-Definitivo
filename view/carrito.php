<?php
require_once __DIR__ . '/../config/app.php';
require_once '../config/database.php';
session_start();
require_once __DIR__ . '/../config/session_timeout.php';

if (!isset($_SESSION['user_id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['user_id_usuario'];

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_usuario = ? AND estado = 'pendiente' ORDER BY fecha DESC LIMIT 1");
$stmt->execute([$id_usuario]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

$id_pedido = $_GET['id_pedido'] ?? null;
if (!$id_pedido && !$pedido) {
    die("No se recibió un pedido válido.");
}

$id_pedido = $pedido['id_pedido'] ?? $id_pedido;

$stmt = $pdo->prepare("
    SELECT pd.*, p.nombre, p.precio_unitario 
    FROM pedido_detalle pd
    JOIN productos p ON pd.id_producto = p.id_producto
    WHERE pd.id_pedido = ?
");
$stmt->execute([$id_pedido]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($productos as $prod) {
    $total += $prod['cantidad'] * $prod['precio_unitario'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación del Pedido - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-2xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <h1 class="text-3xl font-bold text-center text-emerald-700 mb-8">Confirmar Pedido</h1>

            <?php if (isset($_GET['pago'])): ?>
                <?php if ($_GET['pago'] === 'exitoso'): ?>
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700">
                        <i class="bi bi-check-circle me-2"></i> ¡Pedido confirmado! Se ha programado entrega en efectivo.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="mb-8">
                <p class="text-sm text-slate-600 mb-2"><strong>Pedido ID:</strong> <?= $id_pedido ?></p>
            </div>

            <h2 class="text-xl font-bold text-slate-900 mb-4">Resumen del Pedido</h2>
            
            <div class="overflow-x-auto mb-8">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 border-b-2 border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Producto</th>
                            <th class="px-4 py-3 text-center font-semibold">Cantidad</th>
                            <th class="px-4 py-3 text-right font-semibold">Precio</th>
                            <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($productos as $prod): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3"><?= htmlspecialchars($prod['nombre']) ?></td>
                                <td class="px-4 py-3 text-center"><?= $prod['cantidad'] ?></td>
                                <td class="px-4 py-3 text-right">$<?= number_format($prod['precio_unitario'], 2) ?></td>
                                <td class="px-4 py-3 text-right font-semibold">$<?= number_format($prod['cantidad'] * $prod['precio_unitario'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mb-8">
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-6 w-full sm:w-96">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Total a Pagar</h3>
                    <p class="text-3xl font-bold text-emerald-600">$<?= number_format($total, 2) ?></p>
                </div>
            </div>

            <!-- Información de pago en efectivo -->
            <div class="rounded-xl bg-blue-50 border border-blue-200 p-6 mb-6">
                <div class="flex items-start gap-3">
                    <i class="bi bi-info-circle text-xl text-blue-600 mt-1"></i>
                    <div>
                        <h4 class="font-bold text-blue-900 mb-2">Método de Pago: Efectivo</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li><i class="bi bi-check-circle me-2"></i>Deberás pagar en efectivo al momento de la entrega</li>
                            <li><i class="bi bi-check-circle me-2"></i>El dinero se recibe directamente del agricultor</li>
                            <li><i class="bi bi-check-circle me-2"></i>Tu pedido está pendiente de confirmación de entrega</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form action="../controller/confirmar_pedido_efectivo.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_pedido" value="<?= $id_pedido ?>">
                <input type="hidden" name="monto_total" value="<?= $total ?>">

                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-6 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">
                    <i class="bi bi-check-circle me-2"></i> Confirmar Pedido en Efectivo
                </button>
            </form>

            <div class="mt-4 flex gap-2">
                <a href="carritoview.php" class="flex-1 text-center rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="bi bi-arrow-left me-2"></i> Volver al carrito
                </a>
            </div>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>
