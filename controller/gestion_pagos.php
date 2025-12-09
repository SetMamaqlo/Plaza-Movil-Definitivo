<?php
// Pasarela deshabilitada: redirige siempre a pago en efectivo
require_once '../config/app.php';
header('Location: ' . base_url('view/carritoview.php?pago=fallido&error=mercadopago_desactivado'));
exit;
