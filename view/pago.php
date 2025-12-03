<?php
require_once __DIR__ . '/../config/app.php';
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/config/session_timeout.php';

if (!isset($_SESSION['user_id_usuario'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id_pedido'])) {
    header("Location: carrito.php?error=no_pedido");
    exit;
}

$id_pedido = intval($_GET['id_pedido']);
$id_usuario = $_SESSION['user_id_usuario'];

$stmt = $pdo->prepare("
    SELECT p.id_pedido, p.fecha, p.estado, u.nombre_completo AS comprador
    FROM pedidos p
    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
    WHERE p.id_pedido = ? AND p.id_usuario = ?
");
$stmt->execute([$id_pedido, $id_usuario]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header("Location: carrito.php?error=no_pedido");
    exit;
}

$stmtDetalles = $pdo->prepare("
    SELECT d.cantidad, d.precio_unitario, pr.nombre, pr.foto, u.nombre_completo AS agricultor
    FROM pedido_detalle d
    INNER JOIN productos pr ON d.id_producto = pr.id_producto
    INNER JOIN agricultor a ON pr.id_agricultor = a.id_agricultor
    INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
    WHERE d.id_pedido = ?
");
$stmtDetalles->execute([$id_pedido]);
$detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($detalles as $d) {
    $total += $d['precio_unitario'] * $d['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago del Pedido #<?php echo $id_pedido; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-4xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Pago del Pedido #<?php echo $id_pedido; ?></h1>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="rounded-lg bg-slate-50 p-4 border border-slate-200">
                    <p class="text-sm text-slate-600"><strong>Comprador:</strong> <?php echo htmlspecialchars($pedido['comprador']); ?></p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4 border border-slate-200">
                    <p class="text-sm text-slate-600"><strong>Fecha:</strong> <?php echo $pedido['fecha']; ?></p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4 border border-slate-200">
                    <p class="text-sm text-slate-600"><strong>Estado:</strong> <span class="font-semibold text-emerald-600"><?php echo ucfirst($pedido['estado']); ?></span></p>
                </div>
            </div>

            <div class="overflow-x-auto mb-8">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 border-b-2 border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Producto</th>
                            <th class="px-4 py-3 text-left font-semibold">Imagen</th>
                            <th class="px-4 py-3 text-left font-semibold">Agricultor</th>
                            <th class="px-4 py-3 text-center font-semibold">Cantidad</th>
                            <th class="px-4 py-3 text-right font-semibold">Precio Unitario</th>
                            <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($detalles as $d): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3"><?php echo htmlspecialchars($d['nombre']); ?></td>
                                <td class="px-4 py-3">
                                    <img src="../img/<?php echo htmlspecialchars($d['foto']); ?>" width="60" height="60" class="rounded-lg object-cover">
                                </td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($d['agricultor']); ?></td>
                                <td class="px-4 py-3 text-center"><?php echo $d['cantidad']; ?></td>
                                <td class="px-4 py-3 text-right">$<?php echo number_format($d['precio_unitario']); ?></td>
                                <td class="px-4 py-3 text-right font-semibold">$<?php echo number_format($d['precio_unitario'] * $d['cantidad']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mb-8">
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-6 w-full sm:w-96">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Total a Pagar</h3>
                    <p class="text-3xl font-bold text-emerald-600">$<?php echo number_format($total); ?></p>
                </div>
            </div>

            <form action="../controller/gestion_pagos.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                <input type="hidden" name="monto" value="<?php echo $total; ?>">
                
                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-6 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">
                    <i class="bi bi-credit-card me-2"></i> Confirmar Pago
                </button>
            </form>

            <a href="carritoview.php" class="inline-block mt-4 rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="bi bi-arrow-left me-2"></i> Volver al carrito
            </a>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>