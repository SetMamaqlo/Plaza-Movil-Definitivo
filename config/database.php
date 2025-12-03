<?php
// Cargar configuración de .env
require_once __DIR__ . '/app.php';

function parseDbUrl(string $url): array {
    $parts = parse_url($url);
    return [
        'host' => $parts['host'] ?? '',
        'port' => $parts['port'] ?? '3306',
        'db'   => ltrim($parts['path'] ?? '', '/'),
        'user' => $parts['user'] ?? '',
        'pass' => $parts['pass'] ?? '',
    ];
}

$dbUrl = getenv('DB_URL') ?: getenv('DATABASE_URL');
$parsed = $dbUrl ? parseDbUrl($dbUrl) : [];

$db_host = $parsed['host'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$db_port = $parsed['port'] ?? getenv('DB_PORT') ?: '3306';
$db_name = $parsed['db']   ?? getenv('DB_DATABASE') ?: 'railway';
$db_user = $parsed['user'] ?? getenv('DB_USERNAME') ?: 'root';
$db_pass = $parsed['pass'] ?? getenv('DB_PASSWORD') ?: 'FnBzTrcfhiHzSCEjmhaVAkXnsLzgpSAt';

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
$pdo = null;

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);
    error_log("DB OK: {$db_host}:{$db_port}/{$db_name}");
} catch (PDOException $e) {
    error_log("DB FAIL {$db_host}:{$db_port}/{$db_name}: " . $e->getMessage());
    $pdo = null;
    if (getenv('APP_ENV') === 'development') {
        echo "Error de conexión: " . htmlspecialchars($e->getMessage());
    }
}
?>