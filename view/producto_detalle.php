<?php
require_once __DIR__ . '/../config/app.php';
require_once '../config/database.php';
require_once '../model/resena_model.php';
session_start();
require_once __DIR__ . '/config/session_timeout.php';

if (!isset($_GET['id_producto'])) {
    echo "Producto no encontrado.";
    exit;
}

$id_producto = $_GET['id_producto'];

$stmt = $pdo->prepare("
    SELECT p.*, um.nombre AS unidad,
           u.nombre_completo AS agricultor, u.telefono, u.foto AS foto_usuario
    FROM productos p
    JOIN agricultor a ON p.id_agricultor = a.id_agricultor
    JOIN usuarios u ON a.id_usuario = u.id_usuario
    LEFT JOIN unidades_de_medida um ON p.id_unidad = um.id_unidad
    WHERE p.id_producto = ?
");
$stmt->execute([$id_producto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "Producto no encontrado.";
    exit;
}

$rutaImgProducto = "../img/" . $producto['foto'];
if (empty($producto['foto']) || !is_file(__DIR__ . "/../img/" . $producto['foto'])) {
    $rutaImgProducto = "../img/default.png";
}

$rutaImgUsuario = "../img/" . $producto['foto_usuario'];
if (empty($producto['foto_usuario']) || !is_file(__DIR__ . "/../img/" . $producto['foto_usuario'])) {
    $rutaImgUsuario = "../img/default.png";
}

$resenas = ResenaModel::obtenerResenas($id_producto);
$stmt = $pdo->prepare("SELECT id_agricultor FROM productos WHERE id_producto = ?");
$stmt->execute([$id_producto]);
$id_agricultor = $stmt->fetchColumn();
$calificacion = ResenaModel::promedioAgricultor($id_agricultor);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estrellas'], $_POST['comentario'])) {
    $estrellas = (int)$_POST['estrellas'];
    $comentario = trim($_POST['comentario']);
    $id_usuario = $_SESSION['user_id_usuario'];
    ResenaModel::agregarResena($id_producto, $id_usuario, $estrellas, $comentario);
    header("Location: producto_detalle.php?id_producto=$id_producto");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']); ?> - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Imagen del producto -->
            <div class="flex items-center justify-center bg-white rounded-2xl shadow-lg ring-1 ring-slate-100 p-6">
                <img src="<?php echo $rutaImgProducto; ?>" class="max-h-96 object-cover rounded-lg"
                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>

            <!-- Detalles del producto -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 mb-3"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                    <p class="text-lg text-slate-600"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                </div>

                <div class="bg-emerald-50 rounded-2xl p-6 ring-1 ring-emerald-100">
                    <div class="text-3xl font-bold text-emerald-700 mb-2">
                        $<?php echo number_format($producto['precio_unitario']); ?> / <?php echo htmlspecialchars($producto['unidad'] ?? ''); ?>
                    </div>
                    <p class="text-sm text-slate-600"><i class="bi bi-calendar"></i> Publicado: <?php echo htmlspecialchars($producto['fecha_publicacion']); ?></p>
                </div>

                <!-- Vendedor -->
                <div class="flex items-center gap-4 bg-white rounded-2xl shadow-md ring-1 ring-slate-100 p-6">
                    <img src="<?php echo $rutaImgUsuario; ?>" alt="Agricultor"
                         class="h-16 w-16 rounded-full object-cover ring-2 ring-emerald-100">
                    <div>
                        <h3 class="font-bold text-slate-900"><?php echo htmlspecialchars($producto['agricultor']); ?></h3>
                        <p class="text-sm text-slate-600"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($producto['telefono']); ?></p>
                    </div>
                </div>

                <!-- Botón Comprar -->
                <form action="../controller/carritocontroller.php" method="POST">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                    <button type="submit" class="w-full rounded-xl bg-emerald-600 px-6 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">
                        <i class="bi bi-cart-plus me-2"></i> Añadir al Carrito
                    </button>
                </form>
            </div>
        </div>

        <!-- Reseñas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Reseñas existentes -->
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Reseñas del producto</h2>
                <div class="space-y-4">
                    <?php
                    $ultimasResenas = array_slice($resenas, 0, 3);
                    foreach ($ultimasResenas as $resena):
                    ?>
                    <div class="bg-emerald-50 rounded-xl p-5 ring-1 ring-emerald-100">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-slate-900"><?= htmlspecialchars($resena['nombre_completo']) ?></h4>
                            <span class="text-xs text-slate-600"><?= $resena['fecha'] ?></span>
                        </div>
                        <div class="flex gap-1 text-amber-400 mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= $resena['estrellas'] ? '-fill' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-slate-700"><?= htmlspecialchars($resena['comentario']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($resenas) > 3): ?>
                    <button type="button" onclick="document.getElementById('todasResenas').classList.remove('hidden')" 
                            class="mt-4 rounded-xl border border-emerald-600 px-6 py-2 text-emerald-600 font-semibold hover:bg-emerald-50">
                        Ver todas las reseñas (<?= count($resenas) ?>)
                    </button>
                <?php endif; ?>
            </div>

            <!-- Formulario dejar reseña -->
            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-slate-100 p-6">
                <h3 class="text-xl font-bold text-slate-900 mb-4">Deja tu reseña</h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Puntuación:</label>
                        <div class="flex gap-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" id="star<?= $i ?>" name="estrellas" value="<?= $i ?>" required class="hidden">
                                <label for="star<?= $i ?>" class="cursor-pointer text-3xl text-slate-300 hover:text-amber-400 transition">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Comentario:</label>
                        <textarea name="comentario" maxlength="250" required 
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                  rows="4" placeholder="Comparte tu experiencia..."></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        Enviar reseña
                    </button>
                </form>
            </div>
        </div>

        <!-- Modal todas las reseñas -->
        <div id="todasResenas" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Todas las reseñas</h2>
                    <button onclick="document.getElementById('todasResenas').classList.add('hidden')" class="text-2xl text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                <div class="space-y-4">
                    <?php foreach ($resenas as $resena): ?>
                    <div class="bg-emerald-50 rounded-xl p-4 ring-1 ring-emerald-100">
                        <div class="flex justify-between mb-2">
                            <h4 class="font-semibold"><?= htmlspecialchars($resena['nombre_completo']) ?></h4>
                            <span class="text-xs text-slate-600"><?= $resena['fecha'] ?></span>
                        </div>
                        <div class="flex gap-1 text-amber-400 mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= $resena['estrellas'] ? '-fill' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p><?= htmlspecialchars($resena['comentario']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Calificación del Agricultor -->
        <div class="mt-12 bg-white rounded-2xl shadow-lg ring-1 ring-slate-100 p-6">
            <h3 class="text-xl font-bold text-slate-900 mb-3">Calificación del Agricultor</h3>
            <div class="flex items-center gap-3">
                <div class="flex gap-1 text-amber-400 text-2xl">
                    <?php 
                    $prom = $calificacion['promedio'] ?? 0;
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<i class="bi bi-star' . ($i <= round($prom) ? '-fill' : '') . '"></i>';
                    }
                    ?>
                </div>
                <span class="text-lg font-semibold text-slate-900">
                    <?= number_format($prom, 2) ?>/5 (<?= ($calificacion['total'] ?? 0) ?> votos)
                </span>
            </div>
        </div>

        <!-- Productos recomendados -->
        <div class="mt-14">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Productos recomendados</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $stmtReco = $pdo->prepare("SELECT id_producto, nombre, foto, precio_unitario FROM productos WHERE id_producto != ? ORDER BY RAND() LIMIT 3");
                $stmtReco->execute([$producto['id_producto']]);
                $recomendados = $stmtReco->fetchAll(PDO::FETCH_ASSOC);

                $idsReco = array_column($recomendados, 'id_producto');
                $promediosReco = [];
                if ($idsReco) {
                    $in = implode(',', array_map('intval', $idsReco));
                    $stmtProm = $pdo->query("SELECT id_producto, AVG(estrellas) as promedio FROM producto_resenas WHERE id_producto IN ($in) GROUP BY id_producto");
                    while ($row = $stmtProm->fetch(PDO::FETCH_ASSOC)) {
                        $promediosReco[$row['id_producto']] = $row['promedio'];
                    }
                }

                foreach ($recomendados as $reco):
                    $rutaReco = "../img/" . $reco['foto'];
                    if (empty($reco['foto']) || !is_file(__DIR__ . "/../img/" . $reco['foto'])) {
                        $rutaReco = "../img/default.png";
                    }
                    $promReco = $promediosReco[$reco['id_producto']] ?? 0;
                ?>
                <a href="producto_detalle.php?id_producto=<?php echo $reco['id_producto']; ?>"
                   class="group rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="h-40 overflow-hidden bg-slate-100">
                        <img src="<?php echo $rutaReco; ?>" class="h-full w-full object-cover group-hover:scale-105 transition"
                             alt="<?php echo htmlspecialchars($reco['nombre']); ?>">
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold text-slate-900 line-clamp-1 mb-2"><?php echo htmlspecialchars($reco['nombre']); ?></h4>
                        <div class="flex gap-1 text-amber-400 mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= round($promReco) ? '-fill' : '' ?> text-sm"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-lg font-bold text-emerald-600">$<?php echo number_format($reco['precio_unitario']); ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>

    <script>
        // Manejo de estrellas en reseña
        const stars = document.querySelectorAll('#star1, #star2, #star3, #star4, #star5');
        const labels = document.querySelectorAll('label[for^="star"]');
        
        labels.forEach((label, idx) => {
            label.addEventListener('click', () => {
                stars[idx].checked = true;
                labels.forEach((l, i) => {
                    l.classList.toggle('text-amber-400', i <= idx);
                    l.classList.toggle('text-slate-300', i > idx);
                });
            });
            
            label.addEventListener('mouseover', () => {
                labels.forEach((l, i) => {
                    l.classList.toggle('text-amber-400', i <= idx);
                    l.classList.toggle('text-slate-300', i > idx);
                });
            });
        });

        document.addEventListener('mouseout', () => {
            const checked = document.querySelector('input[name="estrellas"]:checked');
            if (checked) {
                const checkedIdx = Array.from(stars).indexOf(checked);
                labels.forEach((l, i) => {
                    l.classList.toggle('text-amber-400', i <= checkedIdx);
                    l.classList.toggle('text-slate-300', i > checkedIdx);
                });
            }
        });
    </script>
</body>
</html>