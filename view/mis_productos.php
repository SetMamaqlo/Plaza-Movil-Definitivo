<?php
require_once __DIR__ . '/../config/app.php';
session_start();
require_once '../config/database.php';
require_once '../controller/medidas_controller.php';
require_once '../controller/gestion_categorias.php';
require_once __DIR__ . '/config/session_timeout.php';

// Validar sesión y rol
if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] != 3) {
    header("Location: ../index.php");
    exit;
}

$user_id_usuario = $_SESSION['user_id_usuario'];
$id_agricultor = $_SESSION['user_id_agricultor'] ?? null;

// Obtener productos del agricultor
$stmt = $pdo->prepare("
    SELECT p.*, c.nombre AS categoria_nombre, u.nombre AS unidad_nombre
    FROM productos p
    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
    LEFT JOIN unidades_de_medida u ON p.id_unidad = u.id_unidad
    WHERE p.id_agricultor = ?
    ORDER BY p.fecha_publicacion DESC
");
$stmt->execute([$id_agricultor]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías
$stmtCategorias = $pdo->query("SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC");
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Contar productos activos e inactivos
$activos = count(array_filter($productos, fn($p) => $p['estado'] === 'activo'));
$inactivos = count(array_filter($productos, fn($p) => $p['estado'] === 'inactivo'));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Productos - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-6xl px-6 py-12">
        <!-- Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-emerald-700 mb-1">Total de Productos</p>
                        <p class="text-3xl font-bold text-emerald-800"><?= count($productos); ?></p>
                    </div>
                    <i class="bi bi-box-seam text-4xl text-emerald-300"></i>
                </div>
            </div>

            <div class="rounded-2xl bg-green-50 border border-green-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-green-700 mb-1">Productos Activos</p>
                        <p class="text-3xl font-bold text-green-800"><?= $activos; ?></p>
                    </div>
                    <i class="bi bi-check-circle text-4xl text-green-300"></i>
                </div>
            </div>

            <div class="rounded-2xl bg-red-50 border border-red-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-red-700 mb-1">Productos Inactivos</p>
                        <p class="text-3xl font-bold text-red-800"><?= $inactivos; ?></p>
                    </div>
                    <i class="bi bi-x-circle text-4xl text-red-300"></i>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Mis Productos</h1>
            <div class="flex gap-3">
                <button onclick="document.getElementById('modalAgregar').classList.remove('hidden')" 
                        class="rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-6 py-3 transition shadow-lg shadow-emerald-600/30 flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>Añadir Producto
                </button>
                <button type="button" id="btnEliminar" 
                        class="rounded-xl border border-red-600 text-red-600 font-semibold px-6 py-3 transition hover:bg-red-50 flex items-center gap-2">
                    <i class="bi bi-trash"></i>Eliminar Seleccionados
                </button>
            </div>
        </div>

        <form id="formEliminarProductos" method="POST" action="../controller/eliminarproductos.php">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($productos as $producto): ?>
                    <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 overflow-hidden hover:shadow-xl transition <?= $producto['estado'] === 'inactivo' ? 'opacity-60' : ''; ?>">
                        <div class="relative">
                            <img src="<?php echo !empty($producto['foto']) ? '../img/' . htmlspecialchars($producto['foto']) : '../img/default.png'; ?>"
                                class="w-full h-48 object-cover">
                            
                            <!-- Badge de estado -->
                            <div class="absolute top-3 right-3">
                                <?php if ($producto['estado'] === 'activo'): ?>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-500 text-white text-xs font-bold px-3 py-1">
                                        <i class="bi bi-check-circle"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-500 text-white text-xs font-bold px-3 py-1">
                                        <i class="bi bi-x-circle"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start gap-3 mb-3">
                                <input type="checkbox" class="producto-checkbox mt-1" name="productos_a_eliminar[]"
                                    value="<?php echo $producto['id_producto']; ?>">
                                <h3 class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            </div>
                            <p class="text-sm text-slate-600 mb-3"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                            <div class="space-y-2 mb-4">
                                <p class="text-sm"><strong>Precio:</strong> $<?php echo number_format($producto['precio_unitario']); ?> / <?php echo htmlspecialchars($producto['unidad_nombre']); ?></p>
                                <p class="text-sm"><strong>Categoría:</strong> <?php echo htmlspecialchars($producto['categoria_nombre']); ?></p>
                                <p class="text-sm"><strong>Stock:</strong> <?php echo $producto['stock']; ?> unidades</p>
                            </div>
                            <div class="space-y-2">
                                <button type="button" class="w-full rounded-xl border border-emerald-600 text-emerald-600 font-semibold px-4 py-2.5 hover:bg-emerald-50 transition flex items-center justify-center gap-2"
                                    onclick="editarProducto(
                                        <?php echo $producto['id_producto']; ?>,
                                        '<?php echo htmlspecialchars(addslashes($producto['nombre'])); ?>',
                                        '<?php echo htmlspecialchars(addslashes($producto['descripcion'])); ?>',
                                        '<?php echo $producto['precio_unitario']; ?>',
                                        '<?php echo $producto['id_categoria']; ?>',
                                        '<?php echo $producto['stock']; ?>'
                                    )">
                                    <i class="bi bi-pencil"></i>Editar Producto
                                </button>
                                
                                <!-- Botón toggle estado -->
                                <form method="POST" action="../controller/toggle_estado_producto.php" class="w-full">
                                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                    <input type="hidden" name="nuevo_estado" value="<?php echo $producto['estado'] === 'activo' ? 'inactivo' : 'activo'; ?>">
                                    <?php if ($producto['estado'] === 'activo'): ?>
                                        <button type="submit" class="w-full rounded-xl border border-slate-300 text-slate-700 font-semibold px-4 py-2.5 hover:bg-slate-100 transition flex items-center justify-center gap-2 bg-slate-50">
                                            <i class="bi bi-eye-slash"></i>Desactivar Producto
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="w-full rounded-xl border border-green-600 text-green-600 font-semibold px-4 py-2.5 hover:bg-green-50 transition flex items-center justify-center gap-2">
                                            <i class="bi bi-check-circle"></i>Activar Producto
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <!-- Botón eliminar -->
                                <button type="button" class="w-full rounded-xl border border-red-600 text-red-600 font-semibold px-4 py-2.5 hover:bg-red-50 transition flex items-center justify-center gap-2"
                                    onclick="if(confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                                        const form = document.createElement('form');
                                        form.method = 'POST';
                                        form.action = '../controller/eliminarproductos.php';
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'productos_a_eliminar[]';
                                        input.value = <?php echo $producto['id_producto']; ?>;
                                        form.appendChild(input);
                                        document.body.appendChild(form);
                                        form.submit();
                                    }">
                                    <i class="bi bi-trash"></i>Eliminar Producto
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                    <div class="col-span-full text-center py-12">
                        <p class="text-slate-600 text-lg">No tienes productos publicados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Modal Editar Producto -->
    <div id="modalEditar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-900">Editar Producto</h3>
                <button onclick="document.getElementById('modalEditar').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 text-2xl">&times;</button>
            </div>
            <form id="formEditarProducto" method="POST" action="../controller/editarproducto.php" class="space-y-4">
                <input type="hidden" name="id_producto" id="edit_id_producto">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre</label>
                    <input type="text" id="edit_nombre" name="nombre" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Descripción</label>
                    <textarea id="edit_descripcion" name="descripcion" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría</label>
                    <select id="edit_categoria" name="categoria" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Precio</label>
                    <input type="number" step="0.01" id="edit_precio" name="precio" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Stock</label>
                    <input type="number" id="edit_stock" name="stock" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('modalEditar').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-300 text-slate-700 px-4 py-2 text-sm font-semibold hover:bg-slate-50 transition">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-500 transition shadow-lg shadow-emerald-600/30">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Agregar Producto -->
    <div id="modalAgregar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-slate-900">Añadir Nuevo Producto</h2>
                <button onclick="document.getElementById('modalAgregar').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 text-2xl">&times;</button>
            </div>
            <form method="POST" action="../controller/productcontroller.php" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre del Producto</label>
                    <input type="text" name="nombre" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="3" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Precio Unitario</label>
                    <input type="number" step="0.01" name="precio_unitario" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Stock Disponible</label>
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
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Imagen del Producto</label>
                    <input type="file" name="foto" accept="image/*" required class="w-full">
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('modalAgregar').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-300 text-slate-700 px-4 py-3 text-sm font-semibold hover:bg-slate-50 transition flex items-center justify-center gap-2">
                        <i class="bi bi-x-circle"></i>Cancelar
                    </button>
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-600 text-white px-4 py-3 text-sm font-semibold hover:bg-emerald-500 transition flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30">
                        <i class="bi bi-plus-circle"></i>Añadir Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editarProducto(id_producto, nombre, descripcion, precio_unitario, id_categoria, stock) {
            document.getElementById('edit_id_producto').value = id_producto;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_precio').value = precio_unitario;
            document.getElementById('edit_categoria').value = id_categoria;
            document.getElementById('edit_stock').value = stock;
            document.getElementById('modalEditar').classList.remove('hidden');
        }

        document.getElementById('btnEliminar').addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('input[name="productos_a_eliminar[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Selecciona al menos un producto para eliminar.');
                return;
            }
            if (confirm('¿Estás seguro de que deseas eliminar los productos seleccionados?')) {
                document.getElementById('formEliminarProductos').submit();
            }
        });
    </script>
</body>

</html>