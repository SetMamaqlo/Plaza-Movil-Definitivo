<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once '../controller/historial_ventas_controller.php';
require_once __DIR__ . '/../config/session_timeout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-7xl px-6 py-12">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Historial de Ventas</h1>

        <!-- Resumen de Ventas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-2xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg p-6">
                <i class="bi bi-currency-dollar text-3xl mb-3 block"></i>
                <h3 class="text-lg font-semibold mb-2">Total Ventas</h3>
                <p class="text-3xl font-bold">$<?= number_format($total_general, 2); ?></p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg p-6">
                <i class="bi bi-cart-check text-3xl mb-3 block"></i>
                <h3 class="text-lg font-semibold mb-2">Pedidos Totales</h3>
                <p class="text-3xl font-bold"><?= $total_pedidos; ?></p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-lg p-6">
                <i class="bi bi-box-seam text-3xl mb-3 block"></i>
                <h3 class="text-lg font-semibold mb-2">Productos Vendidos</h3>
                <p class="text-3xl font-bold"><?= $total_productos_vendidos; ?></p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="rounded-2xl bg-white shadow-md ring-1 ring-slate-100 p-6 mb-8">
            <h3 class="text-lg font-bold text-slate-900 mb-4"><i class="bi bi-funnel me-2"></i>Filtros</h3>
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Estado del Pedido</label>
                        <select name="estado" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl bg-emerald-600 text-white px-6 py-2 font-semibold hover:bg-emerald-500">
                        <i class="bi bi-filter me-2"></i>Filtrar
                    </button>
                    <a href="historial_ventas.php" class="rounded-xl border border-slate-200 text-slate-700 px-6 py-2 font-semibold hover:bg-slate-50">
                        <i class="bi bi-arrow-clockwise me-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Historial de Ventas -->
        <?php if (count($ventas_agrupadas) === 0): ?>
            <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-12 text-center">
                <i class="bi bi-inbox text-6xl text-slate-300 mb-4 block"></i>
                <h3 class="text-xl font-bold text-slate-900">No tienes ventas registradas</h3>
                <p class="text-slate-600 mt-2">Tus ventas aparecerán aquí cuando los clientes compren tus productos.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($ventas_agrupadas as $pedido): ?>
                    <div class="rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-6 py-4">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h3 class="text-lg font-bold">
                                        <i class="bi bi-receipt me-2"></i>Pedido #<?= htmlspecialchars($pedido['id_pedido']); ?>
                                    </h3>
                                    <p class="text-sm text-emerald-50 mt-1">
                                        <i class="bi bi-calendar me-1"></i><?= htmlspecialchars($pedido['fecha']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-white/30 mb-2 me-2">
                                        <?= htmlspecialchars($pedido['estado']); ?>
                                    </span>
                                    <div class="text-2xl font-bold">
                                        $<?= number_format($pedido['total_pedido'], 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <p class="text-sm text-slate-600"><strong>Cliente:</strong></p>
                                    <p class="text-slate-900"><?= htmlspecialchars($pedido['cliente']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-600"><strong>Email:</strong></p>
                                    <p class="text-slate-900"><?= htmlspecialchars($pedido['cliente_email']); ?></p>
                                </div>
                            </div>

                            <div class="overflow-x-auto mb-6">
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
                                        <?php foreach ($pedido['productos'] as $producto): ?>
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-4 py-3"><strong><?= htmlspecialchars($producto['producto_nombre']); ?></strong></td>
                                                <td class="px-4 py-3 text-center"><?= htmlspecialchars($producto['cantidad']); ?></td>
                                                <td class="px-4 py-3 text-right">$<?= number_format($producto['precio_unitario'], 2); ?></td>
                                                <td class="px-4 py-3 text-right font-semibold">$<?= number_format($producto['total_linea'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex gap-3 flex-wrap">
                                <a href="../controller/generar_comprobante_venta.php?id_pedido=<?= $pedido['id_pedido']; ?>&id_agricultor=<?= $id_agricultor; ?>"
                                   target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 text-sm font-semibold hover:bg-blue-700">
                                    <i class="bi bi-receipt"></i> Comprobante
                                </a>
                                <a href="mailto:<?= $pedido['cliente_email']; ?>?subject=Consulta sobre tu pedido #<?= $pedido['id_pedido']; ?>"
                                   class="inline-flex items-center gap-2 rounded-xl border border-blue-600 text-blue-600 px-4 py-2 text-sm font-semibold hover:bg-blue-50">
                                    <i class="bi bi-envelope"></i> Contactar
                                </a>
                                <div class="relative group">
                                    <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 text-slate-700 px-4 py-2 text-sm font-semibold hover:bg-slate-50">
                                        <i class="bi bi-pencil"></i> Estado
                                    </button>
                                    <div class="hidden group-hover:block absolute bg-white shadow-lg rounded-lg p-2 space-y-1 z-10">
                                        <a href="../controller/actualizar_estado_pedido.php?id_pedido=<?= $pedido['id_pedido']; ?>&estado=pendiente" class="block px-4 py-2 text-sm hover:bg-slate-100 rounded">Pendiente</a>
                                        <a href="../controller/actualizar_estado_pedido.php?id_pedido=<?= $pedido['id_pedido']; ?>&estado=confirmado" class="block px-4 py-2 text-sm hover:bg-slate-100 rounded">Confirmado</a>
                                        <a href="../controller/actualizar_estado_pedido.php?id_pedido=<?= $pedido['id_pedido']; ?>&estado=completado" class="block px-4 py-2 text-sm hover:bg-slate-100 rounded">Completado</a>
                                    </div>
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
