<?php
session_start();
if (!isset($_SESSION['user_id_usuario'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../config/app.php';
require_once '../config/database.php';
require_once '../model/NotificacionModel.php';

$id_pedido = $_GET['id_pedido'] ?? null;
$estado = $_GET['estado'] ?? null;
$id_agricultor = $_SESSION['user_id_usuario'];

if (!$id_pedido || !$estado) {
    $_SESSION['error'] = 'Parámetros inválidos';
    header('Location: ../view/historial_ventas.php');
    exit();
}

// Verificar que el agricultor tiene productos en este pedido
$stmt = $pdo->prepare('
    SELECT COUNT(*) as tiene_productos 
    FROM pedido_detalle pd
    INNER JOIN productos pr ON pd.id_producto = pr.id_producto
    WHERE pd.id_pedido = ? AND pr.id_agricultor = ?
');
$stmt->execute([$id_pedido, $id_agricultor]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$estadosPermitidos = ['pendiente', 'aprobado', 'cancelado', 'entregado'];
if ($result['tiene_productos'] > 0 && in_array($estado, $estadosPermitidos, true)) {
    // Actualizar estado del pedido
    $stmt = $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id_pedido = ?');
    $stmt->execute([$estado, $id_pedido]);

    // Actualizar estado del pago asociado
    $estadoPago = match ($estado) {
        'entregado' => 'completado',
        'cancelado' => 'cancelado',
        default     => 'pendiente',
    };
    $pagoStmt = $pdo->prepare('UPDATE pagos SET estado = ? WHERE id_pedido = ?');
    $pagoStmt->execute([$estadoPago, $id_pedido]);

    // Notificar comprador sobre el cambio de estado
    $usuarioPedidoStmt = $pdo->prepare('SELECT id_usuario FROM pedidos WHERE id_pedido = ? LIMIT 1');
    $usuarioPedidoStmt->execute([$id_pedido]);
    $pedidoUsuario = $usuarioPedidoStmt->fetch(PDO::FETCH_ASSOC);
    if ($pedidoUsuario) {
        $noti = new NotificacionModel($pdo);
        $mensajeEstado = match ($estado) {
            'aprobado' => "Tu pedido #{$id_pedido} fue aprobado por el agricultor.",
            'entregado' => "Tu pedido #{$id_pedido} fue marcado como entregado.",
            'cancelado' => "Tu pedido #{$id_pedido} fue cancelado.",
            default => "El estado de tu pedido #{$id_pedido} cambió a {$estado}.",
        };
        $noti->crear((int)$pedidoUsuario['id_usuario'], $mensajeEstado, base_url('view/historialpedidos.php'));
    }

    $_SESSION['success'] = 'Estado del pedido actualizado correctamente';
} else {
    $_SESSION['error'] = 'No tienes permisos para modificar este pedido';
}

header('Location: ../view/historial_ventas.php');
exit();
?>
