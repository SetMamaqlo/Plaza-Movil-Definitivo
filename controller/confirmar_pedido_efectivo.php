<?php
session_start();
require_once '../config/database.php';

// Flujo deprecated: todos los pedidos se manejan como pago en efectivo contra entrega.
header("Location: ../view/carritoview.php?pedido=creado");
exit;
