<?php
session_start();

require_once '../config/conexion.php';
require_once __DIR__ . '/../config/session_timeout.php';

if (!isset($_SESSION['user_id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Obtener los IDs de productos en el carrito
require_once '../model/carrito_model.php';
require_once '../model/detalle_carrito_model.php';

$carritoModel = new CarritoModel($pdo);
$detalleModel = new DetalleCarritoModel($pdo);

// Verificar usuario logueado
$id_usuario = $_SESSION['user_id_usuario'];

// Obtener carrito del usuario
$carrito = $carritoModel->obtenerCarritoPorUsuario($id_usuario);
$productos = [];

if ($carrito) {
    $productos = $detalleModel->obtenerProductos($carrito['id_carrito']);
}

// Calcular el total del carrito
$total = 0;
foreach ($productos as $producto) {
    $total += $producto['precio_unitario'] * $producto['cantidad'];
}

$pedidoStatus = $_GET['pedido'] ?? null;
$error = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Plaza Movil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-slate-50 text-slate-900">
    <!-- Navbar -->
    <?php include '../navbar.php'; ?>

    <!-- Espacio para que el contenido no quede oculto bajo la navbar fija -->
    <div style="height:70px"></div>

    <div class="mx-auto max-w-4xl px-6 py-12">
        <?php if ($pedidoStatus === 'creado'): ?>
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700">
                <i class="bi bi-check-circle me-2"></i>Pedido generado. Paga en efectivo al recibirlo; queda pendiente hasta que el campesino lo gestione.
            </div>
        <?php elseif ($error === 'no_pedido'): ?>
            <div class="mb-6 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-700">
                <i class="bi bi-exclamation-triangle me-2"></i>No se recibio un pedido valido.
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 mb-8">
            <i class="bi bi-cart3 text-3xl text-emerald-600"></i>
            <h1 class="text-3xl font-bold text-slate-900">Carrito de Compras</h1>
        </div>

        <?php if (empty($productos)): ?>
            <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-12 text-center">
                <i class="bi bi-emoji-frown text-6xl text-slate-300 mb-4 block"></i>
                <p class="text-lg text-slate-600 mb-6">Tu carrito esta vacio.</p>
                <a href="../index.php" class="inline-block rounded-xl bg-emerald-600 text-white font-semibold px-6 py-3 hover:bg-emerald-500 transition">
                    <i class="bi bi-arrow-left me-2"></i>Seguir comprando
                </a>
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-6 mb-6">
                <?php foreach ($productos as $producto): ?>
                    <div class="flex items-center gap-4 py-4 border-b border-slate-200 last:border-b-0">
                        <img src="../img/<?php echo htmlspecialchars($producto['foto']); ?>" 
                             class="h-20 w-20 rounded-lg object-cover">
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-900"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="text-sm text-slate-600"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        </div>
                        <!-- Formulario para editar cantidad -->
                        <form action="../controller/editar_cantidad.php" method="POST" class="flex items-center gap-2">
                            <input type="hidden" name="id_detalle" value="<?php echo $producto['id_detalle']; ?>">
                            <input type="number" name="cantidad" value="<?php echo $producto['cantidad']; ?>" min="1" class="w-20 rounded-lg border border-slate-200 px-3 py-2 text-center text-sm">
                            <button type="submit" class="rounded-lg bg-emerald-600 text-white px-3 py-2 text-sm font-semibold hover:bg-emerald-500">Actualizar</button>
                        </form>
                        <div class="text-right">
                            <p class="text-lg font-bold text-emerald-600">$<?php echo number_format($producto['precio_unitario'] * $producto['cantidad']); ?></p>
                        </div>
                        <!-- Formulario para eliminar producto -->
                        <form action="../controller/eliminar_del_carrito.php" method="POST" class="ml-2">
                            <input type="hidden" name="id_detalle" value="<?php echo $producto['id_detalle']; ?>">
                            <button type="submit" class="text-red-600 hover:text-red-700 font-bold text-lg">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-6 mb-6">
                <h3 class="text-lg font-bold text-slate-900 mb-2">Total</h3>
                <p class="text-3xl font-bold text-emerald-600">$<?php echo number_format($total, 2); ?></p>
            </div>

            <form action="../controller/crear_pedido.php" method="POST" class="mb-4">
                <input type="hidden" name="id_carrito" value="<?php echo $carrito['id_carrito']; ?>">
                <button type="submit" class="w-full rounded-xl bg-emerald-600 text-white font-bold py-4 text-lg hover:bg-emerald-500 transition shadow-lg">
                    <i class="bi bi-cash me-2"></i>Confirmar pedido con pago en efectivo
                </button>
            </form>

            <a href="../index.php" class="inline-block rounded-xl border border-slate-200 text-slate-700 font-semibold px-6 py-3 hover:bg-slate-50 transition">
                <i class="bi bi-arrow-left me-2"></i>Seguir comprando
            </a>
        <?php endif; ?>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Movil. Todos los derechos reservados.
    </footer>
</body>
</html>
