<?php
// Mapa centralizado de rutas para que los enlaces sean fáciles de ajustar.
// Usa BASE_URL (definido en app.php) para construir URLs absolutas.
require_once __DIR__ . '/app.php';

$LINKS = [
    'home'        => '',
    'quienes'     => 'view/quienes_somos.php',
    'dashboard'   => 'view/dashboard.php',
    'carrito'     => 'view/carritoview.php',
    'perfil'      => 'view/perfil.php',
    'pedidos_ag'  => 'view/pedidos_agricultor.php',
    'mis_productos' => 'view/mis_productos.php',
    'historial_ventas' => 'view/historial_ventas.php',
    'carga_masiva'    => 'view/carga_masiva.php',
    'login'      => 'view/login.php',
    'registro'   => 'view/register.php',
    'logout'     => 'controller/logout.php',
];

function link_to(string $key, string $fallback = ''): string
{
    global $LINKS;
    $path = $LINKS[$key] ?? $fallback;
    return base_url($path);
}
?>
