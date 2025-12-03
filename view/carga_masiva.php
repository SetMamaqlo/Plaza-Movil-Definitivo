<?php
require_once __DIR__ . '/../config/app.php';

// Verificar si la sesión ya está iniciada antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] != 3) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../controller/carga_masiva_controller.php';
require_once __DIR__ . '/config/session_timeout.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga Masiva de Productos - Agricultor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>
    
    <div class="mx-auto max-w-4xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900 mb-2">
                    <i class="bi bi-upload me-2 text-emerald-600"></i>Carga Masiva de Productos
                </h1>
                <p class="text-slate-600">Sube múltiples productos a través de un archivo CSV</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="mb-6 p-4 rounded-xl <?= $tipoMensaje == 'success' ? 'bg-green-50 border border-green-200 text-green-700' : ($tipoMensaje == 'warning' ? 'bg-yellow-50 border border-yellow-200 text-yellow-700' : 'bg-red-50 border border-red-200 text-red-700'); ?>">
                    <h5 class="font-semibold mb-1">
                        <?php echo $tipoMensaje == 'success' ? '✅ Éxito' : ($tipoMensaje == 'warning' ? '⚠️ Advertencia' : '❌ Error'); ?>
                    </h5>
                    <p class="text-sm"><?php echo htmlspecialchars($mensaje); ?></p>
                </div>
            <?php endif; ?>

            <!-- Instrucciones -->
            <div class="mb-8 rounded-xl bg-blue-50 border border-blue-200 p-6">
                <h3 class="font-semibold text-blue-900 mb-3"><i class="bi bi-info-circle me-2"></i>Instrucciones:</h3>
                <p class="text-sm text-blue-800 mb-3">El archivo CSV debe tener este formato (la columna "Unidad" es opcional):</p>
                
                <div class="rounded-lg bg-white overflow-x-auto mt-3">
                    <table class="text-sm w-full">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold">Nombre</th>
                                <th class="px-4 py-2 text-left font-semibold">Descripción</th>
                                <th class="px-4 py-2 text-left font-semibold">Categoría</th>
                                <th class="px-4 py-2 text-left font-semibold">Precio</th>
                                <th class="px-4 py-2 text-left font-semibold">Stock</th>
                                <th class="px-4 py-2 text-left font-semibold">Unidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-2">Manzana Roja</td>
                                <td class="px-4 py-2">Manzana fresca de la región</td>
                                <td class="px-4 py-2">Frutas</td>
                                <td class="px-4 py-2">2500</td>
                                <td class="px-4 py-2">100</td>
                                <td class="px-4 py-2">Kilo</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Formulario -->
            <form action="carga_masiva.php" method="POST" enctype="multipart/form-data" id="formCarga" class="mb-8">
                <div class="mb-6">
                    <label for="archivo_excel" class="block text-sm font-semibold text-slate-700 mb-3">
                        <i class="bi bi-file-earmark-csv me-2"></i>Seleccionar archivo CSV:
                    </label>
                    <input type="file" class="w-full rounded-xl border-2 border-dashed border-slate-300 px-4 py-6 text-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 cursor-pointer" 
                           id="archivo_excel" name="archivo_excel" accept=".csv" required>
                    <p class="text-xs text-slate-600 mt-2">
                        <i class="bi bi-info-circle me-1"></i>Tamaño máximo: 10MB. Formato permitido: CSV (.csv)
                    </p>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="mis_productos.php" class="rounded-xl border border-slate-200 text-slate-700 font-semibold px-6 py-3 hover:bg-slate-50 transition">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Mis Productos
                    </a>
                    <button type="submit" class="rounded-xl bg-emerald-600 text-white font-semibold px-6 py-3 hover:bg-emerald-500 transition shadow-lg" id="btnCargar">
                        <i class="bi bi-upload me-2"></i>Iniciar Carga Masiva
                    </button>
                </div>
            </form>

            <!-- Descarga plantilla -->
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-6 mb-6">
                <h4 class="font-semibold text-amber-900 mb-3"><i class="bi bi-download me-2"></i>¿No tienes una plantilla?</h4>
                <a href="../controller/descargar_plantilla.php?tipo=csv" 
                   class="inline-flex items-center gap-2 rounded-xl border border-amber-300 bg-white text-amber-700 font-semibold px-6 py-3 hover:bg-amber-50 transition">
                    <i class="bi bi-file-earmark-text"></i> Descargar Plantilla CSV
                </a>
            </div>

            <!-- Categorías disponibles -->
            <div class="rounded-xl bg-slate-100 p-6">
                <h4 class="font-semibold text-slate-900 mb-3"><i class="bi bi-list-check me-2"></i>Categorías Disponibles</h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($categorias as $cat): ?>
                        <span class="rounded-full bg-slate-600 text-white text-xs font-semibold px-3 py-1">
                            <?= htmlspecialchars($cat['nombre']); ?> (ID: <?= $cat['id_categoria']; ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-slate-600 mt-3"><i class="bi bi-info-circle me-1"></i>Usa exactamente estos nombres en la columna "Categoría" del CSV</p>
            </div>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>

    <script>
        document.getElementById('formCarga').addEventListener('submit', function(e) {
            const archivo = document.getElementById('archivo_excel').files[0];
            const btnCargar = document.getElementById('btnCargar');
            
            if (archivo) {
                const fileName = archivo.name.toLowerCase();
                if (!fileName.endsWith('.csv')) {
                    e.preventDefault();
                    alert('Solo se permiten archivos CSV (.csv)');
                    return;
                }
                
                btnCargar.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Procesando...';
                btnCargar.disabled = true;
            }
        });
    </script>
</body>
</html>