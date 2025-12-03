<?php
require_once __DIR__ . '/config/app.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id_rol'])) {
    header("Location: " . base_url("view/login.php"));
    exit;
}

$id_categoria   = isset($_GET['id_categoria']) ? (int) $_GET['id_categoria'] : null;
$busqueda       = $_GET['busqueda'] ?? '';
$categoria_filtro = $_GET['categoria_filtro'] ?? '';
$precio_min     = $_GET['precio_min'] ?? '';
$precio_max     = $_GET['precio_max'] ?? '';

try {
    $sql = "SELECT p.*, u.nombre AS unidad, c.nombre AS categoria_nombre
            FROM productos p
            LEFT JOIN unidades_de_medida u ON p.id_unidad = u.id_unidad
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            WHERE 1=1";
    $params = [];

    if ($id_categoria) {
        $sql .= " AND p.id_categoria = ?";
        $params[] = $id_categoria;
        $catStmt = $pdo->prepare("SELECT nombre FROM categoria WHERE id_categoria = ?");
        $catStmt->execute([$id_categoria]);
        $categoriaSeleccionada = $catStmt->fetchColumn();
    }
    if (!empty($busqueda)) {
        $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
        $params[] = "%$busqueda%";
        $params[] = "%$busqueda%";
    }
    if (!empty($categoria_filtro)) {
        $sql .= " AND p.id_categoria = ?";
        $params[] = $categoria_filtro;
    }
    if (!empty($precio_min)) {
        $sql .= " AND p.precio_unitario >= ?";
        $params[] = $precio_min;
    }
    if (!empty($precio_max)) {
        $sql .= " AND p.precio_unitario <= ?";
        $params[] = $precio_max;
    }

    $sql .= " ORDER BY p.fecha_publicacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $e) {
    error_log("Error BD en index.php: " . $e->getMessage());
    die("Error al cargar los productos. Revisa el log del servidor.");
}

$promedios = [];
$promStmt = $pdo->query("SELECT id_producto, AVG(estrellas) as promedio FROM producto_resenas GROUP BY id_producto");
while ($row = $promStmt->fetch(PDO::FETCH_ASSOC)) {
    $promedios[$row['id_producto']] = $row['promedio'];
}
$categoriasFiltro = $pdo->query("SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>"><!-- opcional si necesitas estilos propios -->
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include __DIR__ . '/navbar.php'; ?>

    <!-- Hero -->
    <section class="relative isolate overflow-hidden bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 text-white">
        <div class="absolute inset-0 opacity-20 mix-blend-overlay bg-[radial-gradient(circle_at_20%_20%,white,transparent_35%),radial-gradient(circle_at_80%_0,white,transparent_25%)]"></div>
        <div class="mx-auto max-w-6xl px-6 py-14 lg:flex lg:items-center lg:justify-between lg:gap-10">
            <div class="relative z-10 max-w-2xl space-y-4">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1 text-sm font-semibold ring-1 ring-white/30">Plaza Móvil · Agro Mercado</p>
                <h1 class="text-4xl font-bold leading-tight sm:text-5xl">
                    Productos frescos directo del campo
                </h1>
                <p class="text-lg text-emerald-50">
                    Explora frutas, verduras y más, con filtros rápidos y tarjetas limpias. Encuentra lo que necesitas en segundos.
                </p>
                <div class="flex flex-wrap gap-3">
                    <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/20">Envíos locales</span>
                    <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/20">Productores verificados</span>
                    <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/20">Pagos seguros</span>
                </div>
            </div>
            <div class="relative z-10 mt-10 lg:mt-0">
                <div class="w-full max-w-md rounded-2xl bg-white/10 p-6 shadow-2xl ring-1 ring-white/30 backdrop-blur">
                    <p class="text-sm font-semibold text-emerald-50 mb-3">Productos publicados</p>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/25">
                            <p class="text-2xl font-bold"><?= $stmt->rowCount(); ?></p>
                            <p class="text-emerald-50">Total activos</p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/25">
                            <p class="text-2xl font-bold"><?= count($categoriasFiltro); ?></p>
                            <p class="text-emerald-50">Categorías</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-emerald-50/80">Refresca la página para ver nuevas publicaciones recientes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Buscador y filtros -->
    <section class="mx-auto max-w-6xl px-6 -mt-10">
        <div class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-100">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-12">
                <div class="md:col-span-5">
                    <label class="text-sm font-semibold text-slate-600">Buscar</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 5.65 5.65a7.5 7.5 0 0 0 11 11Z" /></svg>
                        </span>
                        <input name="busqueda" value="<?= htmlspecialchars($busqueda); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="Nombre o descripción..." />
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="text-sm font-semibold text-slate-600">Categoría</label>
                    <select name="categoria_filtro" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        <option value="">Todas</option>
                        <?php foreach ($categoriasFiltro as $cat): ?>
                            <option value="<?= $cat['id_categoria']; ?>" <?= $categoria_filtro == $cat['id_categoria'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-600">Precio mín.</label>
                    <input type="number" name="precio_min" min="0" step="0.01" value="<?= htmlspecialchars($precio_min); ?>" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="0" />
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-600">Precio máx.</label>
                    <input type="number" name="precio_max" min="0" step="0.01" value="<?= htmlspecialchars($precio_max); ?>" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="100000" />
                </div>

                <div class="md:col-span-12 flex flex-wrap items-end justify-between gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:-translate-y-0.5 hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200">
                        <span>Buscar</span>
                    </button>
                    <?php if ($busqueda || $categoria_filtro || $precio_min || $precio_max): ?>
                        <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                            <?php if ($busqueda): ?><span class="rounded-full bg-slate-100 px-3 py-1">Búsqueda: "<?= htmlspecialchars($busqueda); ?>"</span><?php endif; ?>
                            <?php if ($categoria_filtro): ?>
                                <?php $catNombre = $categoriasFiltro[array_search($categoria_filtro, array_column($categoriasFiltro, 'id_categoria'))]['nombre']; ?>
                                <span class="rounded-full bg-slate-100 px-3 py-1">Categoría: <?= htmlspecialchars($catNombre); ?></span>
                            <?php endif; ?>
                            <?php if ($precio_min): ?><span class="rounded-full bg-slate-100 px-3 py-1">Precio mín: $<?= htmlspecialchars($precio_min); ?></span><?php endif; ?>
                            <?php if ($precio_max): ?><span class="rounded-full bg-slate-100 px-3 py-1">Precio máx: $<?= htmlspecialchars($precio_max); ?></span><?php endif; ?>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700"><?= $stmt->rowCount(); ?> productos</span>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <!-- Productos -->
    <section class="mx-auto mt-10 max-w-6xl px-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">
                <?= ($id_categoria && isset($categoriaSeleccionada)) ? "Productos de " . htmlspecialchars($categoriaSeleccionada) : "Productos publicados"; ?>
            </h2>
        </div>

        <?php if ($stmt->rowCount() > 0): ?>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <?php
                $delay = 0.08; $idx = 0;
                while ($producto = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $promedio = $promedios[$producto['id_producto']] ?? 0;
                ?>
                <a href="<?= base_url('view/producto_detalle.php?id_producto=' . $producto['id_producto']) ?>"
                   class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-2xl">
                    <div class="h-40 overflow-hidden bg-slate-100">
                        <img src="img/<?= htmlspecialchars($producto['foto']); ?>" alt="<?= htmlspecialchars($producto['nombre']); ?>"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    </div>
                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-base font-semibold text-slate-900 line-clamp-1"><?= htmlspecialchars($producto['nombre']); ?></h3>
                            <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700"><?= htmlspecialchars($producto['categoria_nombre']); ?></span>
                        </div>
                        <p class="text-sm text-slate-600 line-clamp-2"><?= htmlspecialchars($producto['descripcion']); ?></p>
                        <div class="flex items-center gap-1 text-amber-400">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 <?= $i <= round($promedio) ? 'fill-amber-400' : 'fill-none stroke-amber-300'; ?>" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.2 5.01c.08.19.26.32.46.34l5.42.43c.48.04.67.64.3.94l-4.13 3.44a.56.56 0 0 0-.18.55l1.27 5.28a.56.56 0 0 1-.84.6l-4.63-2.8a.56.56 0 0 0-.58 0l-4.63 2.8a.56.56 0 0 1-.84-.6l1.27-5.28a.56.56 0 0 0-.18-.55L2.1 10.22a.56.56 0 0 1 .3-.94l5.42-.43c.2-.02.38-.15.46-.34l2.2-5.01Z"/></svg>
                            <?php endfor; ?>
                            <?php if ($promedio > 0): ?>
                                <span class="text-xs font-semibold text-slate-500">(<?= number_format($promedio, 2); ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto flex items-center justify-between">
                            <span class="text-lg font-bold text-emerald-600">$<?= number_format($producto['precio_unitario']); ?> / <?= htmlspecialchars($producto['unidad']); ?></span>
                            <span class="text-xs text-slate-500">Publicado <?= date('d/m', strtotime($producto['fecha_publicacion'])); ?></span>
                        </div>
                    </div>
                </a>
                <?php $idx++; } ?>
            </div>
        <?php else: ?>
            <div class="mt-8 flex flex-col items-center rounded-2xl bg-white p-10 text-center shadow-lg ring-1 ring-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 5.65 5.65a7.5 7.5 0 0 0 11 11Z" /></svg>
                <h3 class="mt-4 text-lg font-semibold text-slate-800">No se encontraron productos</h3>
                <p class="text-sm text-slate-500">Ajusta los filtros e inténtalo de nuevo.</p>
                <a href="<?= base_url('index.php') ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg hover:bg-emerald-500">Ver todos</a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Por categoría -->
    <?php if (!$busqueda && !$categoria_filtro && !$precio_min && !$precio_max): ?>
    <section class="mx-auto mt-14 max-w-6xl px-6">
        <h2 class="text-2xl font-bold">Productos por categoría</h2>
        <div class="mt-6 space-y-10">
            <?php
            $categoriasStmt = $pdo->query("SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC");
            $categorias = $categoriasStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categorias as $categoria):
                $categoriaNombre = htmlspecialchars($categoria['nombre']);
                $categoriaId = $categoria['id_categoria'];
                $productosStmt = $pdo->prepare("SELECT p.*, u.nombre AS unidad FROM productos p LEFT JOIN unidades_de_medida u ON p.id_unidad = u.id_unidad WHERE p.id_categoria = ? ORDER BY p.fecha_publicacion DESC");
                $productosStmt->execute([$categoriaId]);
            ?>
            <div>
                <div class="flex items-center gap-3">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <h3 class="text-xl font-semibold text-slate-900"><?= $categoriaNombre; ?></h3>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <?php while ($producto = $productosStmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <?php $promedio = $promedios[$producto['id_producto']] ?? 0; ?>
                    <a href="<?= base_url('view/producto_detalle.php?id_producto=' . $producto['id_producto']) ?>"
                       class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="h-36 overflow-hidden bg-slate-100">
                            <img src="img/<?= htmlspecialchars($producto['foto']); ?>" alt="<?= htmlspecialchars($producto['nombre']); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        </div>
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            <h4 class="text-base font-semibold text-slate-900 line-clamp-1"><?= htmlspecialchars($producto['nombre']); ?></h4>
                            <div class="flex items-center gap-1 text-amber-400">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 <?= $i <= round($promedio) ? 'fill-amber-400' : 'fill-none stroke-amber-300'; ?>" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.2 5.01c.08.19.26.32.46.34l5.42.43c.48.04.67.64.3.94l-4.13 3.44a.56.56 0 0 0-.18.55l1.27 5.28a.56.56 0 0 1-.84.6l-4.63-2.8a.56.56 0 0 0-.58 0l-4.63 2.8a.56.56 0 0 1-.84-.6l1.27-5.28a.56.56 0 0 0-.18-.55L2.1 10.22a.56.56 0 0 1 .3-.94l5.42-.43c.2-.02.38-.15.46-.34l2.2-5.01Z"/></svg>
                                <?php endfor; ?>
                                <?php if ($promedio > 0): ?>
                                    <span class="text-xs font-semibold text-slate-500">(<?= number_format($promedio, 2); ?>)</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-slate-600 line-clamp-2"><?= htmlspecialchars($producto['descripcion']); ?></p>
                            <div class="mt-auto text-sm font-semibold text-emerald-600">$<?= number_format($producto['precio_unitario']); ?> / <?= htmlspecialchars($producto['unidad']); ?></div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>

    <script>
        function limpiarFiltro(tipo) {
            const url = new URL(window.location.href);
            url.searchParams.delete(tipo);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>