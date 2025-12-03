<?php
// Configuración de timeout de sesión (15 minutos)
define('SESSION_TIMEOUT', 15 * 60); // 15 minutos en segundos

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay una sesión activa
if (isset($_SESSION['user_id_usuario'])) {
    $tiempo_actual = time();
    $tiempo_ultimo_acceso = $_SESSION['ultimo_acceso'] ?? $tiempo_actual;
    
    // Calcular diferencia de tiempo
    $diferencia = $tiempo_actual - $tiempo_ultimo_acceso;
    
    // Si la inactividad supera el timeout, destruir la sesión
    if ($diferencia > SESSION_TIMEOUT) {
        // Destruir sesión
        session_unset();
        session_destroy();
        
        // Redirigir al login
        header("Location: " . (isset($_SERVER['BASE_URL']) ? $_SERVER['BASE_URL'] : '/Plaza-Movil-Definitivo') . "/view/login.php?timeout=1");
        exit;
    }
    
    // Actualizar el tiempo del último acceso
    $_SESSION['ultimo_acceso'] = $tiempo_actual;
}
?>
