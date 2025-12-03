<?php
// Incluir el timeout de sesión al inicio
require_once __DIR__ . '/config/session_timeout.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$carritoModel = null;
$detalleModel = null;
$totalProductos = 0;

if (isset($pdo) && ($pdo instanceof PDO)) {
    require_once __DIR__ . '/model/carrito_model.php';
    require_once __DIR__ . '/model/detalle_carrito_model.php';

    $carritoModel = new CarritoModel($pdo);
    $detalleModel = new DetalleCarritoModel($pdo);

    if (isset($_SESSION['user_id_usuario'])) {
        $id_usuario = $_SESSION['user_id_usuario'];
        $carrito = $carritoModel->obtenerCarritoPorUsuario($id_usuario);

        if ($carrito) {
            $totalProductos = $detalleModel->contarProductosUnicos($carrito['id_carrito']);
        }
    }
} else {
    error_log('navbar.php: sin conexión a BD, se omiten consultas de carrito y categorías.');
    $totalProductos = 0;
}

$busqueda = $_GET['busqueda'] ?? '';
$categoria_filtro = $_GET['categoria_filtro'] ?? '';
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';

$navbarCategorias = [];
try {
    if (isset($pdo) && ($pdo instanceof PDO)) {
        $catStmt = $pdo->query("SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC");
        $navbarCategorias = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error al obtener categorias en navbar: " . $e->getMessage());
}

$navbarUserName = 'Invitado';
$navbarUserRole = 'Visitante';

if (isset($_SESSION['nombre'])) {
    $navbarUserName = $_SESSION['nombre'];
} elseif (isset($_SESSION['user_id_usuario'])) {
    $navbarUserName = 'Usuario';
}

// Obtener el rol del usuario
if (isset($_SESSION['user_id_rol'])) {
    $roleMap = [1 => 'Administrador', 2 => 'Comprador', 3 => 'Agricultor'];
    $navbarUserRole = $roleMap[$_SESSION['user_id_rol']] ?? 'Usuario';
}
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php if (isset($_SESSION['user_id_usuario']) && !isset($_SESSION['bienvenida_mostrada'])): ?>
    <?php $_SESSION['bienvenida_mostrada'] = true; ?>
    <div id="toastBienvenida" class="fixed top-20 right-6 z-50 animate-slideIn bg-gradient-to-r from-emerald-400/90 to-emerald-500/90 text-white rounded-xl shadow-2xl ring-1 ring-emerald-300/40 p-4 flex items-center gap-3 max-w-sm backdrop-blur-sm">
        <i class="bi bi-hand-thumbs-up text-xl flex-shrink-0"></i>
        <div class="flex-1">
            <p class="font-semibold">¡Bienvenido, <?= htmlspecialchars($navbarUserName); ?>!</p>
            <p class="text-sm text-emerald-50/80">Estás en modo: <?= htmlspecialchars($navbarUserRole); ?></p>
        </div>
        <button onclick="document.getElementById('toastBienvenida').classList.add('animate-slideOut')" class="text-emerald-50 hover:text-white">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(450px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(450px);
                opacity: 0;
            }
        }
        
        .animate-slideIn {
            animation: slideIn 0.4s ease-out;
        }
        
        .animate-slideOut {
            animation: slideOut 0.4s ease-in forwards;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toastBienvenida');
            if (toast) {
                setTimeout(function() {
                    toast.classList.add('animate-slideOut');
                    setTimeout(function() {
                        toast.style.display = 'none';
                    }, 400);
                }, 3000);
            }
        });
    </script>
<?php endif; ?>

<nav class="sticky top-0 z-50 border-b border-slate-100 bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4">
        <div class="flex items-center gap-3">
            <button id="navbar-toggle" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 hover:bg-slate-100 focus:outline-none lg:hidden" type="button" aria-label="Abrir menú">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a class="flex items-center gap-3" href="<?= base_url() ?>/index.php">
                <img src="<?= base_url()?>/img/logohorizontal.png" alt="Logo" class="h-10 w-auto">
                <span class="hidden text-lg font-bold text-emerald-700 sm:inline">Plaza Móvil</span>
            </a>
        </div>

        <!-- Búsqueda con filtros integrados (Desktop) -->
        <div class="hidden flex-1 lg:block">
            <details class="relative">
                <summary class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:bg-slate-50">
                    <i class="bi bi-search text-slate-400"></i>
                    <span class="text-slate-600">
                        <?php 
                        if ($busqueda || $categoria_filtro || $precio_min || $precio_max) {
                            echo 'Filtros activos (' . (int)(!empty($busqueda)) + (int)(!empty($categoria_filtro)) + (int)(!empty($precio_min)) + (int)(!empty($precio_max)) . ')';
                        } else {
                            echo 'Buscar productos...';
                        }
                        ?>
                    </span>
                    <i class="bi bi-chevron-down ml-auto text-xs text-slate-500"></i>
                </summary>
                <div class="absolute top-full left-0 right-0 mt-2 w-full rounded-xl bg-white p-4 shadow-xl ring-1 ring-slate-200">
                    <form method="GET" action="<?= base_url() ?>/index.php" class="space-y-4">
                        <!-- Búsqueda por texto -->
                        <div>
                            <label class="text-xs font-semibold text-slate-600">Buscar por nombre o descripción</label>
                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input name="busqueda" value="<?= htmlspecialchars($busqueda); ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="Tomates, frutas, verduras...">
                            </div>
                        </div>

                        <!-- Filtros en grid -->
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Categoría</label>
                                <select name="categoria_filtro" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                                    <option value="">Todas</option>
                                    <?php foreach ($navbarCategorias as $cat): ?>
                                        <option value="<?= $cat['id_categoria']; ?>" <?= $categoria_filtro == $cat['id_categoria'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Precio mín.</label>
                                <input type="number" name="precio_min" min="0" step="0.01" value="<?= htmlspecialchars($precio_min); ?>" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="0">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Precio máx.</label>
                                <input type="number" name="precio_max" min="0" step="0.01" value="<?= htmlspecialchars($precio_max); ?>" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="100000">
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="<?= base_url() ?>/index.php" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </details>
        </div>

        <div class="ml-auto hidden items-center gap-3 lg:flex">
            <a class="text-sm font-semibold text-slate-700 transition hover:text-emerald-600" href="<?= base_url() ?>/index.php">Inicio</a>
            <a class="text-sm font-semibold text-slate-700 transition hover:text-emerald-600" href="<?= base_url() ?>/view/quienes_somos.php">¿Quiénes Somos?</a>

            <?php if (isset($_SESSION['user_id_rol']) && $_SESSION['user_id_rol'] == 1): ?>
                <a class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" href="<?= base_url() ?>/view/dashboard.php">Dashboard</a>
            <?php endif; ?>

            <a class="relative inline-flex items-center justify-center rounded-full border border-slate-200 bg-white p-2 text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700" href="<?= base_url() ?>/view/carritoview.php">
                <i class="bi bi-cart3 text-lg"></i>
                <?php if ($totalProductos > 0): ?>
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-semibold text-white"><?php echo $totalProductos; ?></span>
                <?php endif; ?>
            </a>

            <details class="relative">
                <summary class="flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                        <i class="bi bi-person-fill"></i>
                    </span>
                    <span class="max-w-[140px] truncate"><?= htmlspecialchars($navbarUserName); ?></span>
                    <i class="bi bi-chevron-down text-xs text-slate-500"></i>
                </summary>
                <div class="absolute right-0 mt-2 w-56 rounded-xl bg-white p-2 shadow-xl ring-1 ring-slate-200">
                    <a class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="<?= base_url() ?>/view/perfil.php">
                        <i class="bi bi-person"></i> Mi Perfil
                    </a>
                    <?php if (isset($_SESSION['user_id_rol']) && $_SESSION['user_id_rol'] == 3): ?>
                        <a class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="<?= base_url() ?>/view/pedidos_agricultor.php">
                            <i class="bi bi-receipt"></i> Mis Pedidos
                        </a>
                        <a class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="<?= base_url() ?>/view/mis_productos.php">
                            <i class="bi bi-basket"></i> Mis Productos
                        </a>
                        <a class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="<?= base_url() ?>/view/historial_ventas.php">
                            <i class="bi bi-graph-up"></i> Historial de Ventas
                        </a>
                        <a class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="<?= base_url() ?>/view/carga_masiva.php">
                            <i class="bi bi-upload"></i> Carga Masiva
                        </a>
                        <hr class="my-2 border-slate-100">
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id_rol']) && $_SESSION['user_id_rol'] == 1): ?>
                        <a class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="<?= base_url() ?>/view/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <hr class="my-2 border-slate-100">
                    <?php endif; ?>

                    <form action="<?= base_url() ?>/controller/logincontroller.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <!-- Panel móvil de búsqueda y filtros -->
    <div id="navbar-mobile-panel" class="hidden border-t border-slate-100 bg-white/95 px-4 pb-4 pt-3 shadow-sm lg:hidden">
        <form method="GET" action="<?= base_url() ?>/index.php" class="space-y-3">
            <!-- Búsqueda por texto -->
            <div>
                <label class="text-xs font-semibold text-slate-600">Buscar por nombre o descripción</label>
                <div class="relative mt-2">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input name="busqueda" value="<?= htmlspecialchars($busqueda); ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="Tomates, frutas, verduras...">
                </div>
            </div>

            <!-- Filtros en grid mobile -->
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="text-xs font-semibold text-slate-600">Categoría</label>
                    <select name="categoria_filtro" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-2 text-xs outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        <option value="">Todas</option>
                        <?php foreach ($navbarCategorias as $cat): ?>
                            <option value="<?= $cat['id_categoria']; ?>" <?= $categoria_filtro == $cat['id_categoria'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Precio mín.</label>
                    <input type="number" name="precio_min" min="0" step="0.01" value="<?= htmlspecialchars($precio_min); ?>" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-2 text-xs outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="0">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Precio máx.</label>
                    <input type="number" name="precio_max" min="0" step="0.01" value="<?= htmlspecialchars($precio_max); ?>" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-2 text-xs outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" placeholder="100000">
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?= base_url() ?>/index.php" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap gap-2">
            <a class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" href="<?= base_url() ?>/index.php">Inicio</a>
            <a class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" href="<?= base_url() ?>/view/quienes_somos.php">¿Quiénes Somos?</a>

            <?php if (isset($_SESSION['user_id_rol']) && $_SESSION['user_id_rol'] == 1): ?>
                <a class="rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100" href="<?= base_url() ?>/view/dashboard.php">Dashboard</a>
            <?php endif; ?>

            <a class="relative inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700" href="<?= base_url() ?>/view/carritoview.php">
                <i class="bi bi-cart3"></i>
                <?php if ($totalProductos > 0): ?>
                    <span class="inline-flex items-center justify-center rounded-full bg-red-500 px-2 text-xs font-semibold text-white"><?php echo $totalProductos; ?></span>
                <?php endif; ?>
            </a>

            <a class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="<?= base_url() ?>/view/perfil.php">
                <i class="bi bi-person"></i> Perfil
            </a>
            <form action="<?= base_url() ?>/controller/logincontroller.php" method="POST" class="w-full">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="mt-1 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Botón flotante de regreso (discreto, esquina superior izquierda) -->
<button id="back-float-btn" class="fixed top-20 left-6 z-40 inline-flex items-center gap-2 rounded-lg bg-slate-800/80 backdrop-blur text-white px-3 py-2 text-sm font-semibold transition hover:bg-slate-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-slate-400" title="Volver atrás" onclick="window.history.back()">
    <i class="bi bi-arrow-left text-base"></i>
    <span class="hidden sm:inline">Atrás</span>
</button>

<!-- Botones flotantes -->
<div id="chatbot-float-btn" class="fixed bottom-28 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-slate-800 text-white shadow-xl ring-4 ring-white/60 transition hover:bg-emerald-600" title="Chatbot">
    <i class="bi bi-robot text-2xl"></i>
</div>

<div id="pqrs-float-btn" class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-xl ring-4 ring-white/60 transition hover:bg-emerald-500" title="PQRS">
    <i class="bi bi-chat-dots text-2xl"></i>
</div>

<!-- Chatbot panel -->
<link rel="stylesheet" href="<?= base_url() ?>/css/chatbot.css">
<div id="chatbot-container" class="fixed inset-y-10 right-6 z-50 hidden w-80 flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
    <div class="flex items-center justify-between bg-emerald-600 px-4 py-3 text-white">
        <span class="flex items-center gap-2 font-semibold"><i class="bi bi-robot"></i> Chatbot</span>
        <button id="chatbot-close-btn" type="button" class="text-lg font-bold hover:text-white/80">&times;</button>
    </div>
    <div id="chatbot-body" class="flex-1 space-y-2 overflow-y-auto bg-slate-50 px-3 py-3 text-sm">
        <div class="message bot-message rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-slate-100">¡Hola! Soy tu asistente virtual. ¿En qué puedo ayudarte?</div>
    </div>
    <div class="border-t border-slate-100 bg-white px-3 py-3">
        <form class="flex gap-2">
            <input id="chatbot-input" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" placeholder="Escribe tu mensaje..." autocomplete="off">
            <button id="chatbot-send-btn" type="button" class="rounded-lg bg-emerald-600 px-3 py-2 text-white transition hover:bg-emerald-500">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </div>
</div>
<script src="<?= base_url() ?>/controller/chatbot.js"></script>

<!-- Modal PQRS (Tailwind) -->
<div id="modalPQRS" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur">
  <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase text-emerald-600">PQRS</p>
            <h3 class="text-xl font-bold text-slate-900">Peticiones, Quejas, Reclamos y Sugerencias</h3>
            <p class="mt-1 text-sm text-slate-500">Déjanos tus comentarios o adjunta un archivo.</p>
        </div>
        <button id="pqrs-close" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Cerrar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form class="mt-4 space-y-3" method="POST" action="<?= base_url() ?>/controller/registrar_pqrs.php" enctype="multipart/form-data">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="tipo" class="text-sm font-semibold text-slate-700">Tipo</label>
                <select class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" name="tipo" id="tipo" required>
                    <option value="">Seleccione...</option>
                    <option value="peticion">Petición</option>
                    <option value="queja">Queja</option>
                    <option value="reclamo">Reclamo</option>
                    <option value="sugerencia">Sugerencia</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="asunto" class="text-sm font-semibold text-slate-700">Asunto</label>
                <input type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" name="asunto" id="asunto" maxlength="150" required>
            </div>
            <div class="sm:col-span-2">
                <label for="descripcion" class="text-sm font-semibold text-slate-700">Descripción</label>
                <textarea class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" name="descripcion" id="descripcion" rows="3" maxlength="250" required></textarea>
            </div>
            <div class="sm:col-span-2">
                <label for="adjunto" class="text-sm font-semibold text-slate-700">Adjuntar archivo (opcional)</label>
                <input type="file" class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100" name="adjunto" id="adjunto" accept="image/*,application/pdf">
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
            <button type="button" id="pqrs-cancel" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancelar</button>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200">
                <i class="bi bi-send"></i> Enviar PQRS
            </button>
        </div>
    </form>
  </div>
</div>

<?php if (isset($_GET['pqrs']) && $_GET['pqrs'] === 'ok'): ?>
<script>
    alert('PQRS registrada con éxito');
</script>
<?php endif; ?>

<script>
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMobilePanel = document.getElementById('navbar-mobile-panel');
    if (navbarToggle && navbarMobilePanel) {
        navbarToggle.addEventListener('click', () => {
            navbarMobilePanel.classList.toggle('hidden');
        });
    }

    const chatbotBtn = document.getElementById('chatbot-float-btn');
    const chatbotPanel = document.getElementById('chatbot-container');
    const chatbotClose = document.getElementById('chatbot-close-btn');
    if (chatbotBtn && chatbotPanel) {
        chatbotBtn.addEventListener('click', () => chatbotPanel.classList.toggle('hidden'));
    }
    if (chatbotClose && chatbotPanel) {
        chatbotClose.addEventListener('click', () => chatbotPanel.classList.add('hidden'));
    }

    const pqrsBtn = document.getElementById('pqrs-float-btn');
    const pqrsModal = document.getElementById('modalPQRS');
    const pqrsClose = document.getElementById('pqrs-close');
    const pqrsCancel = document.getElementById('pqrs-cancel');
    const closePqrs = () => pqrsModal && pqrsModal.classList.add('hidden');

    if (pqrsBtn && pqrsModal) pqrsBtn.addEventListener('click', () => pqrsModal.classList.remove('hidden'));
    if (pqrsClose) pqrsClose.addEventListener('click', closePqrs);
    if (pqrsCancel) pqrsCancel.addEventListener('click', closePqrs);
    if (pqrsModal) {
        pqrsModal.addEventListener('click', (e) => {
            if (e.target === pqrsModal) closePqrs();
        });
    }
</script>
