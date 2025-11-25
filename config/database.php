<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$dbUrl = getenv("DB_URL");

if (!$dbUrl) {
    die("Error: Variable de entorno DB_URL no configurada");
}

// Convertir URL de PostgreSQL a DSN compatible con PDO
$dsn = str_replace("postgresql://", "pgsql:host=", $dbUrl);
$dsn = preg_replace("/:(\d+)\//", ";port=$1;dbname=", $dsn);

try {
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false
    ]);
    // Descomentar solo para debug
    // error_log("Conectado a PostgreSQL correctamente");
} catch (PDOException $e) {
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Error de conexión a la base de datos. Revisa el log del servidor.");
}
?>