<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once '../config/conexion.php';
require_once '../model/NotificacionModel.php';

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

    // Pasar los productos del carrito al pedido y recolectar agricultores
    $stmtDetalles = $pdo->prepare("
        SELECT cd.id_producto, cd.cantidad, p.precio_unitario, p.id_unidad, p.id_agricultor
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
    $agricultores = [];
    foreach ($detalles as $d) {
        $stmtInsert->execute([
            $id_pedido,
            $d['id_producto'],
            $d['cantidad'],
            $d['precio_unitario'],
            $d['id_unidad']
        ]);
        $total += $d['precio_unitario'] * $d['cantidad'];
        if (!empty($d['id_agricultor'])) {
            $agricultores[] = (int)$d['id_agricultor'];
        }
    }
    $agricultores = array_unique($agricultores);

    // Registrar pago en efectivo pendiente
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
