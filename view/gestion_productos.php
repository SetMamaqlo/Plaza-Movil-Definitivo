<?php
require_once __DIR__ . '/../config/app.php';
require_once '../config/database.php';
require_once '../controller/medidas_controller.php';
require_once '../controller/gestion_categorias.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/session_timeout.php';

$id_rol = isset($_SESSION['user_id_rol']) ? (int) $_SESSION['user_id_rol'] : null;

if ($id_rol !== 1) {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.nombre AS categoria, u.nombre AS unidades_de_medida, agr.nombre_completo AS agricultor
    FROM productos p
    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
    LEFT JOIN unidades_de_medida u ON p.id_unidad = u.id_unidad
    LEFT JOIN agricultor agr_rel ON p.id_agricultor = agr_rel.id_agricultor
    LEFT JOIN usuarios agr ON agr_rel.id_usuario = agr.id_usuario
    ORDER BY p.fecha_publicacion DESC
");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-7xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Gestión de Productos</h1>
                <button type="button" onclick="document.getElementById('modalAgregar').classList.remove('hidden')" 
                        class="rounded-xl bg-emerald-600 text-white px-6 py-2 font-semibold hover:bg-emerald-500">
                    <i class="bi bi-plus-circle me-2"></i>Añadir Producto
                </button>
            </div>

            <!-- Tabla de productos -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-emerald-50 border-b-2 border-emerald-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">ID</th>
                            <th class="px-4 py-3 text-left font-semibold">Nombre</th>
                            <th class="px-4 py-3 text-left font-semibold">Precio</th>
                            <th class="px-4 py-3 text-left font-semibold">Stock</th>
                            <th class="px-4 py-3 text-left font-semibold">Categoría</th>
                            <th class="px-4 py-3 text-left font-semibold">Agricultor</th>
                            <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($productos as $producto): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3"><?= htmlspecialchars($producto['id_producto']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($producto['nombre']); ?></td>
                                <td class="px-4 py-3">$<?= number_format($producto['precio_unitario']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($producto['stock']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($producto['categoria']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($producto['agricultor'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-3 text-center space-x-2">
                                    <form action="../controller/gestion_productos.php" method="POST" class="inline">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_producto" value="<?= $producto['id_producto']; ?>">
                                        <button type="submit" class="rounded-lg bg-red-600 text-white px-3 py-1 text-xs font-semibold hover:bg-red-700" onclick="return confirm('¿Estás seguro?');">
                                            <i class="bi bi-trash"></i>Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Producto -->
    <div id="modalAgregar" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-slate-900">Añadir Producto</h3>
                <button onclick="document.getElementById('modalAgregar').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 text-2xl">&times;</button>
            </div>
            <form action="../controller/productcontroller.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre</label>
                    <input type="text" name="nombre" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="3" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Precio</label>
                    <input type="number" step="0.01" name="precio_unitario" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Stock</label>
                    <input type="number" name="stock" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría</label>
                    <select name="id_categoria" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id_categoria']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Imagen</label>
                    <input type="file" name="foto" accept="image/*" required class="w-full">
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('modalAgregar').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Añadir</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>
