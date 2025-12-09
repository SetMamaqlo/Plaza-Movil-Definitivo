<?php
require_once '../config/conexion.php';

class PagoModel {
    public static function crearPago($id_pedido, $monto, $metodo, $estado, $transaccion_id, $id_cliente, $nombre_cliente) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO pagos (id_pedido, monto, moneda, metodo, estado, transaccion_id, fecha_pago) VALUES (?, ?, 'COP', ?, ?, ?, NOW())");
        $stmt->execute([$id_pedido, $monto, $metodo, $estado, $transaccion_id]);
    }
}
?>
