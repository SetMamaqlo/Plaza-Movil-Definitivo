<?php
require_once '../config/database.php';
session_start();
require_once __DIR__ . '/config/session_timeout.php';

if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] != 1) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("
    SELECT 
        p.id_pago, p.fecha_pago, p.monto_total, p.metodo, p.estado,
        u.nombre_completo AS cliente,
        ped.id_pedido
    FROM pagos p
    JOIN usuarios u ON u.id_usuario = p.id_usuario
    JOIN pedidos ped ON ped.id_pedido = p.id_pedido
    ORDER BY p.fecha_pago DESC
");
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-7xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Gestión de Pagos en Efectivo</h1>
                <a href="dashboard.php" class="rounded-xl border border-slate-200 text-slate-700 px-6 py-2 font-semibold hover:bg-slate-50">
                    <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                    <i class="bi bi-check-circle me-2"></i> Estado actualizado correctamente
                </div>
            <?php endif; ?>

            <?php if (empty($pagos)): ?>
                <div class="rounded-2xl bg-slate-50 p-8 text-center">
                    <i class="bi bi-inbox text-6xl text-slate-300 mb-4 block"></i>
                    <p class="text-lg text-slate-600">No hay pagos registrados en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-purple-50 border-b-2 border-purple-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">ID Pago</th>
                                <th class="px-4 py-3 text-left font-semibold">Pedido</th>
                                <th class="px-4 py-3 text-left font-semibold">Cliente</th>
                                <th class="px-4 py-3 text-left font-semibold">Fecha</th>
                                <th class="px-4 py-3 text-right font-semibold">Monto</th>
                                <th class="px-4 py-3 text-left font-semibold">Método</th>
                                <th class="px-4 py-3 text-center font-semibold">Estado</th>
                                <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($pagos as $pago): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($pago['id_pago']); ?></td>
                                    <td class="px-4 py-3">#<?= htmlspecialchars($pago['id_pedido']); ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($pago['cliente']); ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($pago['fecha_pago']); ?></td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600">$<?= number_format($pago['monto_total'], 2); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            <i class="bi bi-cash-coin me-1"></i> <?= htmlspecialchars($pago['metodo']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?= $pago['estado'] === 'completado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?= ucfirst(htmlspecialchars($pago['estado'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($pago['estado'] === 'pendiente'): ?>
                                            <form action="../controller/actualizar_estado_pago.php" method="POST" class="inline">
                                                <input type="hidden" name="id_pago" value="<?= $pago['id_pago']; ?>">
                                                <input type="hidden" name="estado" value="completado">
                                                <button type="submit" class="rounded-lg bg-green-600 text-white px-3 py-1 text-xs font-semibold hover:bg-green-700" onclick="return confirm('¿Marcar como completado?');">
                                                    <i class="bi bi-check-circle me-1"></i>Completar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-500"><i class="bi bi-check-all me-1"></i>Completado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>