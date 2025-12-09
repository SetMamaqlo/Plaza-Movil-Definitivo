<?php
session_start();
require_once '../config/conexion.php'; // Crea $pdo
require_once __DIR__ . '/../config/app.php';
require_once '../model/NotificacionModel.php';

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
    $stmtProducto = $pdo->prepare("SELECT precio_unitario, id_unidad, id_agricultor FROM productos WHERE id_producto = ?");
    $stmtDetalle = $pdo->prepare("
        INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario, id_unidad)
        VALUES (?, ?, ?, ?, ?)
    ");

    $total = 0;
    $agricultores = [];
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
        if (!empty($producto['id_agricultor'])) {
            $agricultores[] = (int)$producto['id_agricultor'];
        }
    }
    $agricultores = array_unique($agricultores);

    // Vaciar carrito después de generar pedido
    unset($_SESSION['carrito']);

    // 3. Registrar pago pendiente en efectivo
    $stmtPago = $pdo->prepare("
        INSERT INTO pagos (id_pedido, proveedor, transaccion_id, monto, moneda, estado, metodo, fecha_pago)
        VALUES (?, 'Efectivo', NULL, ?, 'COP', 'pendiente', 'Efectivo', NOW())
    ");
    $stmtPago->execute([$id_pedido, $total]);

    // Notificaciones: comprador y agricultores
    $notiModel = new NotificacionModel($pdo);
    $notiModel->crear($id_usuario, "Tu pedido #{$id_pedido} fue creado y está pendiente.", base_url('view/historialpedidos.php'));

    if ($agricultores) {
        $agriStmt = $pdo->prepare("SELECT a.id_agricultor, u.id_usuario FROM agricultor a INNER JOIN usuarios u ON a.id_usuario = u.id_usuario WHERE a.id_agricultor IN (" . implode(',', array_fill(0, count($agricultores), '?')) . ")");
        $agriStmt->execute($agricultores);
        $agriUsuarios = $agriStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($agriUsuarios as $ag) {
            $notiModel->crear((int)$ag['id_usuario'], "Nuevo pedido #{$id_pedido} pendiente de aprobación.", base_url('view/pedidos_agricultor.php'));
        }
    }

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
