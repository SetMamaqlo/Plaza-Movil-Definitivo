<?php
require_once __DIR__ . '/config/app.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = env('DB_HOST');
$port = env('DB_PORT');
$db   = env('DB_NAME');
$user = env('DB_USERNAME');
$pass = env('DB_PASSWORD');

echo "<pre>";
echo "Intentando conectar a $host:$port / BD=$db\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "✅ Conectado correctamente\n";
} catch (PDOException $e) {
    echo "❌ ERROR PDO ({$e->getCode()}): {$e->getMessage()}\n";
}
