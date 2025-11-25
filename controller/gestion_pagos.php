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

// Configurar MercadoPago (cambia MercadoPagoConfig por SDK)


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
        'payment_methods' => [
            'excluded_payment_methods' => [],
            'excluded_payment_types' => [],
        ],
         'metadata' => [
            'user_id' => $id_usuario,
            'pedido_id' => $id_pedido,
        ]
        
    ]);
} catch (Exception $e) {
    error_log('Error al crear preferencia: ' . $e->getMessage());
    header('Location: ' . base_url('view/carrito.php?error=pago'));
    exit;
}


// Verifica si las columnas preference_id, proveedor y monto existen en la tabla pagos
$columns = $pdo->query("SHOW COLUMNS FROM pagos")->fetchAll(PDO::FETCH_COLUMN);
$hasPreferenceId = in_array('preference_id', $columns);
$hasProveedor = in_array('proveedor', $columns);
$hasMonto = in_array('monto', $columns);

// Guardar el registro inicial en la tabla pagos con estado "pendiente"
if ($hasPreferenceId && $hasProveedor && $hasMonto) {
    $stmt = $pdo->prepare("INSERT INTO pagos (id_pedido, preference_id, proveedor, transaccion_id, monto, moneda, estado, metodo)
                           VALUES (?, ?, 'MercadoPago', NULL, ?, 'COP', 'pendiente', 'checkout')");
    $stmt->execute([$id_pedido, $preference->id, $total]);
} elseif ($hasPreferenceId && $hasMonto) {
    $stmt = $pdo->prepare("INSERT INTO pagos (id_pedido, preference_id, transaccion_id, monto, moneda, estado, metodo)
                           VALUES (?, ?, NULL, ?, 'COP', 'pendiente', 'checkout')");
    $stmt->execute([$id_pedido, $preference->id, $total]);
} elseif ($hasProveedor && $hasMonto) {
    $stmt = $pdo->prepare("INSERT INTO pagos (id_pedido, proveedor, transaccion_id, monto, moneda, estado, metodo)
                           VALUES (?, 'MercadoPago', NULL, ?, 'COP', 'pendiente', 'checkout')");
    $stmt->execute([$id_pedido, $total]);
} elseif ($hasMonto) {
    $stmt = $pdo->prepare("INSERT INTO pagos (id_pedido, transaccion_id, monto, moneda, estado, metodo)
                           VALUES (?, NULL, ?, 'COP', 'pendiente', 'checkout')");
    $stmt->execute([$id_pedido, $total]);
} else {
    $stmt = $pdo->prepare("INSERT INTO pagos (id_pedido, transaccion_id, moneda, estado, metodo)
                           VALUES (?, NULL, 'COP', 'pendiente', 'checkout')");
    $stmt->execute([$id_pedido]);
}

// Redirigir al checkout de MercadoPago
header("Location: " . $preference->init_point);
exit;

