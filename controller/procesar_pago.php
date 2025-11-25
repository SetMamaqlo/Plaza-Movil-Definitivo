<?php
require_once '../config/auth.php';
require_once '../config/conexion.php';
require_once '../config/mercadopago.php';

// Verificar usuario
requireLogin();

$id_usuario = $_SESSION['user_id_usuario'];
$id_pedido = $_POST['id_pedido'] ?? null;

if (!$id_pedido) {
    header('Location: ' . base_url('view/carrito.php?error=pedido'));
    exit;
}

// Traer productos del pedido
$stmt = $pdo->prepare("
    SELECT pd.*, p.nombre, p.precio_unitario 
    FROM pedido_detalle pd
    JOIN productos p ON pd.id_producto = p.id_producto
    WHERE pd.id_pedido = ?
");
$stmt->execute([$id_pedido]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$productos) {
     header('Location: ' . base_url('view/carrito.php?error=productos'));
    exit;
}


$items = [];
$total = 0;
foreach ($productos as $prod) {
    $items[] = [
        "title" => $prod['nombre'],
        "quantity" => (int) $prod['cantidad'],
        "currency_id" => "COP",
        "unit_price" => (float) $prod['precio_unitario']
    ];
    $total += $prod['cantidad'] * $prod['precio_unitario'];
}

try {
    $client = mpClient();
    $preference = $client->create([
         'items' => $items,
        'back_urls' => buildBackUrls('controller/confirmar_pago.php'),
        'auto_return' => 'approved',
        'metadata' => [
            'user_id' => $id_usuario,
            'pedido_id' => $id_pedido,
        ],
    ]);
} catch (Exception $e) {
    error_log('Error al crear preferencia: ' . $e->getMessage());
    header('Location: ' . base_url('view/carrito.php?error=pago'));
    exit;
}

// Guardar el registro inicial en la tabla pagos con estado "pendiente"
// Usamos preference_id temporalmente, luego en confirmar_pago.php lo actualizamos con payment_id real
$stmt = $pdo->prepare("INSERT INTO pagos (id_pedido, preference_id, proveedor, transaccion_id, monto, moneda, estado, metodo)
                       VALUES (?, ?, 'MercadoPago', ?, ?, 'COP', 'pendiente', 'checkout')");
$stmt->execute([$id_pedido, $preference->id, $preference->id, $total]);

// Redirigir al checkout de MercadoPago
header("Location: " . $preference->init_point);
exit;
