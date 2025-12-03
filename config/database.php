<?php
// Cargar configuración de .env
require_once __DIR__ . '/app.php';

// Obtener credenciales de .env (Railway)
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_DATABASE') ?: 'railway';
$db_user = getenv('DB_USERNAME') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';

// Crear DSN para conexión
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

// Variable global $pdo
$pdo = null;

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);
    
    error_log("✅ Conectado a la BD: {$db_host}:{$db_port}/{$db_name}");
    
} catch (PDOException $e) {
    error_log("❌ Error de conexión a BD: " . $e->getMessage());
    $pdo = null;
    
    // Mostrar error amigable solo en desarrollo
    if (getenv('APP_ENV') === 'development') {
        echo "Error de conexión: " . htmlspecialchars($e->getMessage());
    }
}
?>