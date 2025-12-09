<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/login.php");
    exit;
}

$id_pedido = intval($_POST['id_pedido']);
$monto_total = floatval($_POST['monto_total']);
$id_usuario = $_SESSION['user_id_usuario'];

try {
    // Verificar que el pedido pertenece al usuario
    $stmt = $pdo->prepare("SELECT id_pedido FROM pedidos WHERE id_pedido = ? AND id_usuario = ?");
    $stmt->execute([$id_pedido, $id_usuario]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Pedido no válido");
    }

    // Actualizar estado del pedido a "confirmado"
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'confirmado' WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);

    // Registrar la transacción de pago en efectivo
$stmt = $pdo->prepare("
        INSERT INTO pagos (id_pedido, monto, moneda, metodo, fecha_pago, estado)
        VALUES (?, ?, 'COP', 'Efectivo', NOW(), 'pendiente')
    ");
$stmt->execute([$id_pedido, $monto_total]);
    
    // Obtener el ID del pago insertado
    $id_pago = $pdo->lastInsertId();

    // Redirigir a carrito con confirmación
    header("Location: ../view/carritoview.php?pago=exitoso&id_pago=" . $id_pago);
    exit;

} catch (Exception $e) {
    error_log("Error en confirmar_pedido_efectivo.php: " . $e->getMessage());
    header("Location: ../view/carritoview.php?pago=fallido&error=" . urlencode($e->getMessage()));
    exit;
}
