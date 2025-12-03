<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] != 1 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/login.php");
    exit;
}

$id_pago = intval($_POST['id_pago']);
$estado = $_POST['estado'];

// Validar estado
if (!in_array($estado, ['pendiente', 'completado'])) {
    header("Location: ../view/gestion_pagos.php?error=estado_invalido");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE pagos SET estado = ? WHERE id_pago = ?");
    $stmt->execute([$estado, $id_pago]);

    header("Location: ../view/gestion_pagos.php?success=1");
    exit;

} catch (Exception $e) {
    error_log("Error al actualizar estado de pago: " . $e->getMessage());
    header("Location: ../view/gestion_pagos.php?error=1");
    exit;
}
