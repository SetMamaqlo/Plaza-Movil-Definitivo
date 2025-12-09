<?php
require_once __DIR__ . '/app.php';

// Configuracion de timeout de sesion (15 minutos)
define('SESSION_TIMEOUT', 15 * 60); // 15 minutos en segundos

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay una sesion activa
if (isset($_SESSION['user_id_usuario'])) {
    $tiempo_actual = time();
    $tiempo_ultimo_acceso = $_SESSION['ultimo_acceso'] ?? $tiempo_actual;

    // Calcular diferencia de tiempo
    $diferencia = $tiempo_actual - $tiempo_ultimo_acceso;

    // Si la inactividad supera el timeout, destruir la sesion
    if ($diferencia > SESSION_TIMEOUT) {
        // Destruir sesion
        session_unset();
        session_destroy();

        // Redirigir al login usando la base detectada (Render/local)
        header('Location: ' . base_url('view/login.php?timeout=1'));
        exit;
    }

    // Actualizar el tiempo del ultimo acceso
    $_SESSION['ultimo_acceso'] = $tiempo_actual;
}
?>
