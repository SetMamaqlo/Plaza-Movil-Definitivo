<?php
require_once __DIR__ . '/app.php';

$host     = env('DB_HOST');
$dbname   = env('DB_NAME');
$username = env('DB_USERNAME');
$password = env('DB_PASSWORD');
$dbport   = (int) env('DB_PORT');

// IMPORTANTE: aquí SÍ usamos el puerto
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $host,
    $dbport,
    $dbname
);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // 🔴 mientras depuramos: MUESTRA el error real
   //  die('Error de conexión: ' . $e->getMessage());

    // cuando todo funcione, puedes volver a dejarlo así:
     error_log('Error de conexión: ' . $e->getMessage());
     http_response_code(500);
     exit('No se pudo establecer conexión a la base de datos.');
}
