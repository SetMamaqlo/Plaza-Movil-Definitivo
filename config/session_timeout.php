<?php
require_once __DIR__ . '/app.php';

// Configuracion de timeout de sesion (15 minutos)
define('SESSION_TIMEOUT', 15 * 60); // 15 minutos en segundos

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay una sesion activa
if (isset($_SESSION['user_id_usuario'])) {
    // Validar sesion unica por usuario
    $sessionTokenDir = __DIR__ . '/../storage/session_tokens';
    if (!is_dir($sessionTokenDir)) {
        @mkdir($sessionTokenDir, 0777, true);
    }
    $tokenFile = $sessionTokenDir . '/user_' . $_SESSION['user_id_usuario'] . '.token';
    $currentToken = $_SESSION['session_token'] ?? null;
    $storedToken = is_file($tokenFile) ? trim((string)file_get_contents($tokenFile)) : null;

    if (!$currentToken || !$storedToken || !hash_equals($storedToken, $currentToken)) {
        session_unset();
        session_destroy();
        header('Location: ' . base_url('view/login.php?session_revoked=1'));
        exit;
    }

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
