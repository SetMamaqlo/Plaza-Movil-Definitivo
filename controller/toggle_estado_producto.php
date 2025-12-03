<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] != 3) {
    header("Location: ../view/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/mis_productos.php");
    exit;
}

$id_producto = intval($_POST['id_producto']);
$nuevo_estado = $_POST['nuevo_estado'];

// Validar estado
if (!in_array($nuevo_estado, ['activo', 'inactivo'])) {
    header("Location: ../view/mis_productos.php?error=estado_invalido");
    exit;
}

try {
    // Verificar que el producto pertenece al agricultor del usuario
    $stmt = $pdo->prepare("
        SELECT p.id_producto 
        FROM productos p
        INNER JOIN agricultor a ON p.id_agricultor = a.id_agricultor
        WHERE p.id_producto = ? AND a.id_usuario = ?
    ");
    $stmt->execute([$id_producto, $_SESSION['user_id_usuario']]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Producto no encontrado o no tienes permiso");
    }

    // Actualizar estado
    $updateStmt = $pdo->prepare("UPDATE productos SET estado = ? WHERE id_producto = ?");
    $updateStmt->execute([$nuevo_estado, $id_producto]);

    header("Location: ../view/mis_productos.php?success=1");
    exit;

} catch (Exception $e) {
    error_log("Error al cambiar estado de producto: " . $e->getMessage());
    header("Location: ../view/mis_productos.php?error=1");
    exit;
}
