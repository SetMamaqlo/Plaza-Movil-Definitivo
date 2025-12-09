<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id_usuario'])) {
    header("Location: ../view/login.php");
    exit;
}

$id_usuario = $_SESSION['user_id_usuario'];
$id_carrito = $_POST['id_carrito'] ?? null;

if (!$id_carrito) {
    header("Location: ../view/carritoview.php?error=no_pedido");
    exit;
}

try {
    $pdo->beginTransaction();

    // Validar que el carrito pertenezca al usuario
    $stmt = $pdo->prepare("SELECT id_carrito FROM carrito WHERE id_carrito = ? AND id_usuario = ?");
    $stmt->execute([$id_carrito, $id_usuario]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('Carrito no válido para este usuario.');
    }

    // Crear pedido en estado pendiente (contraentrega)
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_usuario, fecha, estado) VALUES (?, NOW(), 'pendiente')");
    $stmt->execute([$id_usuario]);
    $id_pedido = $pdo->lastInsertId();

    // Pasar los productos del carrito al pedido
    $stmtDetalles = $pdo->prepare("
        SELECT cd.id_producto, cd.cantidad, p.precio_unitario, p.id_unidad
        FROM carrito_detalle cd
        INNER JOIN productos p ON cd.id_producto = p.id_producto
        WHERE cd.id_carrito = ?
    ");
    $stmtDetalles->execute([$id_carrito]);
    $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    if (!$detalles) {
        throw new RuntimeException('El carrito está vacío.');
    }

    $stmtInsert = $pdo->prepare("
        INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario, id_unidad)
        VALUES (?, ?, ?, ?, ?)
    ");

    $total = 0;
    foreach ($detalles as $d) {
        $stmtInsert->execute([
            $id_pedido,
            $d['id_producto'],
            $d['cantidad'],
            $d['precio_unitario'],
            $d['id_unidad']
        ]);
        $total += $d['precio_unitario'] * $d['cantidad'];
    }

    // Registrar pago en efectivo pendiente
    $stmtPago = $pdo->prepare("
        INSERT INTO pagos (id_pedido, proveedor, transaccion_id, monto, moneda, estado, metodo, fecha_pago)
        VALUES (?, 'Efectivo', NULL, ?, 'COP', 'pendiente', 'Efectivo', NOW())
    ");
    $stmtPago->execute([$id_pedido, $total]);

    // Vaciar carrito del usuario
    $stmt = $pdo->prepare("DELETE FROM carrito_detalle WHERE id_carrito = ?");
    $stmt->execute([$id_carrito]);
    $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_carrito = ?");
    $stmt->execute([$id_carrito]);

    $pdo->commit();

    header("Location: ../view/carritoview.php?pedido=creado");
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al crear pedido en efectivo: ' . $e->getMessage());
    header("Location: ../view/carritoview.php?error=pago_contado");
    exit;
}
