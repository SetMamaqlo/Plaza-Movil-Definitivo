<?php
// usar el loader propio (config/app.php) que ya carga .env y expone helpers si aplica
require_once __DIR__ . '/app.php';

// Valores (usando .env/entorno si están, fallback a locales)
$db_host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
$db_port = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : '3306');
$db_name = getenv('DB_DATABASE') ?: (defined('DB_DATABASE') ? DB_DATABASE : 'agro_app'); // <- nombre por defecto
$db_user = getenv('DB_USERNAME') ?: (defined('DB_USERNAME') ? DB_USERNAME : 'root');
$db_pass = getenv('DB_PASSWORD') ?: (defined('DB_PASSWORD') ? DB_PASSWORD : '');

// DSN y conexión sencilla
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
$pdo = null;

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    error_log("✅ Conectado a MySQL: {$db_host}:{$db_port}/{$db_name}");
} catch (PDOException $e) {
    error_log("Error de conexión a BD: " . $e->getMessage());
    // Dejar $pdo = null para que el resto del código lo detecte y muestre mensaje amigable
    $pdo = null;
}
?>