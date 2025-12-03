<?php
session_start();

if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] !== 3) {
    header("Location: ../view/login.php");
    exit;
}

require_once '../config/database.php';
require_once '../controller/medidas_controller.php';
require_once '../controller/gestion_categorias.php';
require_once __DIR__ . '/config/session_timeout.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Producto - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gradient-to-br from-emerald-50 to-slate-100 min-h-screen">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-2xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-2xl ring-1 ring-slate-100 p-8">
            <div class="text-center mb-8">
                <i class="bi bi-plus-circle text-4xl text-emerald-600 mb-3 block"></i>
                <h1 class="text-3xl font-bold text-slate-900">Añadir Nuevo Producto</h1>
                <p class="text-slate-600 mt-2">Completa el formulario para publicar tu producto</p>
            </div>

            <form action="../controller/productcontroller.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-2">Nombre del Producto</label>
                    <input type="text" id="nombre" name="nombre" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                           placeholder="Ej: Tomates Frescos">
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-semibold text-slate-700 mb-2">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required 
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                              placeholder="Describe tu producto en detalle..."></textarea>
                </div>

                <!-- Precio -->
                <div>
                    <label for="precio_unitario" class="block text-sm font-semibold text-slate-700 mb-2">Precio Unitario</label>
                    <input type="number" step="0.01" id="precio_unitario" name="precio_unitario" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                           placeholder="0.00">
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-sm font-semibold text-slate-700 mb-2">Stock Disponible</label>
                    <input type="number" id="stock" name="stock" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                           placeholder="0">
                </div>

                <!-- Unidad de Medida -->
                <div>
                    <label for="id_unidad" class="block text-sm font-semibold text-slate-700 mb-2">Unidad de Medida</label>
                    <select id="id_unidad" name="id_unidad" required 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        <option value="">-- Selecciona una unidad --</option>
                        <?php foreach ($medidas as $medida): ?>
                            <option value="<?= htmlspecialchars($medida['id_unidad']) ?>">
                                <?= htmlspecialchars($medida['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Categoría -->
                <div>
                    <label for="id_categoria" class="block text-sm font-semibold text-slate-700 mb-2">Categoría</label>
                    <select id="id_categoria" name="id_categoria" required 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        <option value="">-- Selecciona una categoría --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id_categoria']) ?>">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Imagen -->
                <div>
                    <label for="foto" class="block text-sm font-semibold text-slate-700 mb-2">Imagen del Producto</label>
                    <div class="rounded-xl border-2 border-dashed border-slate-300 p-6 text-center cursor-pointer hover:border-emerald-400 transition" onclick="document.getElementById('foto').click()">
                        <i class="bi bi-image text-3xl text-slate-300 block mb-2"></i>
                        <p class="text-sm font-semibold text-slate-600">Haz clic para seleccionar imagen</p>
                        <p class="text-xs text-slate-500 mt-1">Formatos permitidos: JPG, PNG, GIF (máx. 5MB)</p>
                    </div>
                    <input type="file" id="foto" name="foto" accept="image/*" required class="hidden">
                </div>

                <!-- Botones -->
                <div class="flex gap-3 justify-end pt-6">
                    <a href="mis_productos.php" class="rounded-xl border border-slate-200 text-slate-700 font-semibold px-6 py-3 hover:bg-slate-50 transition">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="rounded-xl bg-emerald-600 text-white font-semibold px-6 py-3 hover:bg-emerald-500 transition shadow-lg">
                        <i class="bi bi-check-circle me-2"></i>Publicar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>

    <script>
        // Preview de imagen
        document.getElementById('foto').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = function(event) {
                    const label = document.querySelector('[onclick*="foto"]');
                    label.innerHTML = `
                        <img src="${event.target.result}" class="h-32 w-32 object-cover rounded-lg mx-auto mb-2">
                        <p class="text-sm font-semibold text-emerald-600">Imagen seleccionada</p>
                        <p class="text-xs text-slate-500 mt-1">${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>