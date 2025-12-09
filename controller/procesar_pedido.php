<?php
session_start();
require_once '../config/conexion.php'; // Crea $pdo

if (!isset($_SESSION['user_id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['user_id_usuario'];

try {
    if (empty($_SESSION['carrito'])) {
        throw new RuntimeException('El carrito está vacío.');
    }

    $pdo->beginTransaction();

    // 1. Crear pedido pendiente (contraentrega)
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_usuario, fecha, estado) VALUES (?, NOW(), 'pendiente')");
    $stmt->execute([$id_usuario]);
    $id_pedido = $pdo->lastInsertId();

    // 2. Insertar detalles del pedido desde la sesión
    $stmtProducto = $pdo->prepare("SELECT precio_unitario, id_unidad FROM productos WHERE id_producto = ?");
    $stmtDetalle = $pdo->prepare("
        INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario, id_unidad)
        VALUES (?, ?, ?, ?, ?)
    ");

    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $id_producto = $item['id_producto'];
        $cantidad = $item['cantidad'];

        $stmtProducto->execute([$id_producto]);
        $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);
        if (!$producto) {
            throw new RuntimeException('Producto no encontrado al procesar pedido.');
        }

        $precio_unitario = $producto['precio_unitario'];
        $id_unidad = $producto['id_unidad'];

        $stmtDetalle->execute([$id_pedido, $id_producto, $cantidad, $precio_unitario, $id_unidad]);
        $total += $precio_unitario * $cantidad;
    }

    // Vaciar carrito después de generar pedido
    unset($_SESSION['carrito']);

    // 3. Registrar pago pendiente en efectivo
    $stmtPago = $pdo->prepare("
        INSERT INTO pagos (id_pedido, proveedor, transaccion_id, monto, moneda, estado, metodo, fecha_pago)
        VALUES (?, 'Efectivo', NULL, ?, 'COP', 'pendiente', 'Efectivo', NOW())
    ");
    $stmtPago->execute([$id_pedido, $total]);

    $pdo->commit();

    header("Location: ../view/carritoview.php?pedido=creado");
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al procesar pedido en efectivo: ' . $e->getMessage());
    header("Location: ../view/carritoview.php?error=pago_contado");
    exit();
}
