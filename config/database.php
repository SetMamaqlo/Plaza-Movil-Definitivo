<?php
// usar el loader propio (config/app.php) que ya carga .env y expone env()
require_once __DIR__ . '/app.php';

// Obtener DB_URL desde env()
$dbUrl = env('DB_URL');

if (!$dbUrl) {
    die("Error: Variable de entorno DB_URL no configurada en .env");
}

try {
    // parsear la URL (scheme://user:pass@host:port/db)
    $url = parse_url($dbUrl);
    $driver = $url['scheme'] ?? 'mysql';
    $host = $url['host'] ?? '127.0.0.1';
    $port = $url['port'] ?? ($driver === 'mysql' ? 3306 : 5432);
    $user = $url['user'] ?? 'root';
    $pass = $url['pass'] ?? '';
    $db = isset($url['path']) ? ltrim($url['path'], '/') : '';

    if ($driver === 'mysql' || $driver === 'mariadb') {
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    } else {
        // asumir pgsql
        $dsn = "pgsql:host={$host};port={$port};dbname={$db}";
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false,
    ]);

    error_log("✅ Conectado a $driver correctamente");

} catch (PDOException $e) {
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Error de conexión a la base de datos. Revisa el log.");
}
?>