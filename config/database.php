<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$dbUrl = getenv("DB_URL");

if (!$dbUrl) {
    die("Error: Variable de entorno DB_URL no configurada en .env");
}

try {
    // Parsear URL de conexión
    $url = parse_url($dbUrl);
    $driver = $url['scheme']; // 'mysql' o 'postgresql'
    $host = $url['host'] ?? 'localhost';
    $port = $url['port'] ?? ($driver === 'mysql' ? 3306 : 5432);
    $db = ltrim($url['path'], '/');
    $user = $url['user'] ?? 'root';
    $pass = $url['pass'] ?? '';

    if ($driver === 'mysql') {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    } else {
        $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false
    ]);

    error_log("✅ Conectado a $driver correctamente");

} catch (PDOException $e) {
    error_log("❌ Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos. Revisa el archivo de log.");
}
?>