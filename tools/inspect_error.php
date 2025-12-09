<?php
// Inspector de errores para usar temporalmente en Render.
// Requiere definir la env var DEBUG_KEY y pasar ?ref=<id>&key=<DEBUG_KEY>

require_once __DIR__ . '/../config/app.php';

if (php_sapi_name() === 'cli') {
    echo "Este script esta pensado para uso web.\n";
    exit;
}

$ref = $_GET['ref'] ?? '';
$key = $_GET['key'] ?? '';
$expected = env('DEBUG_KEY', '');

header('Content-Type: text/html; charset=utf-8');

if (!$ref || !preg_match('/^[a-z0-9_\\-\\.]+$/i', $ref)) {
    http_response_code(400);
    echo "<h3>Solicitud invalida</h3><p>Pasa ?ref=EXCEPTION_REF&amp;key=TU_DEBUG_KEY</p>";
    exit;
}

if (empty($expected) || !$key || !hash_equals($expected, $key)) {
    http_response_code(403);
    echo "<h3>Acceso denegado</h3><p>DEBUG_KEY no esta configurado o la key no coincide.</p>";
    exit;
}

$logDir = __DIR__ . '/../storage/logs';
$logFile = $logDir . "/exception_{$ref}.log";

echo "<meta name='viewport' content='width=device-width,initial-scale=1'><style>body{font-family:system-ui,Arial;color:#111;padding:18px;background:#f7fafc} pre{background:#fff;border:1px solid #e6e6e6;padding:12px;border-radius:8px;overflow:auto}</style>";
echo "<h2>Inspector de error</h2>";
echo "<p><strong>Ref:</strong> " . htmlspecialchars($ref) . "</p>";

// Mostrar log si existe
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    echo "<h3>Contenido del log (" . htmlspecialchars($logFile) . ")</h3>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "<h3>Log no encontrado</h3><p>Busque en: <code>" . htmlspecialchars($logFile) . "</code></p>";
}

// Mostrar variables DB (sin revelar password completa)
$db_host = env('DB_HOST', 'N/D');
$db_port = env('DB_PORT', 'N/D');
$db_name = env('DB_DATABASE', 'N/D');
$db_user = env('DB_USERNAME', 'N/D');
$db_pass = env('DB_PASSWORD', null);

function mask_value($s) {
    if ($s === null || $s === '') return '(empty)';
    $len = strlen($s);
    if ($len <= 3) return str_repeat('*', $len);
    return substr($s, 0, 1) . str_repeat('*', max(1, $len - 2)) . substr($s, -1);
}

echo "<h3>Variables de entorno (solo lectura)</h3>";
echo "<pre>" . htmlspecialchars("DB_HOST={$db_host}\nDB_PORT={$db_port}\nDB_DATABASE={$db_name}\nDB_USERNAME={$db_user}\nDB_PASSWORD=" . mask_value($db_pass)) . "</pre>";

// Intentar conexion PDO y mostrar resultado
echo "<h3>Prueba de conexion PDO (intentando con las vars actuales)</h3>";
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
try {
    $pdoTest = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<p style='color:green;font-weight:600;'>Conexion exitosa</p>";
    $row = $pdoTest->query('SELECT 1')->fetch();
    echo "<pre>Prueba SQL: OK</pre>";
} catch (PDOException $ex) {
    echo "<p style='color:red;font-weight:600;'>Error conectando a la BD:</p>";
    echo "<pre>" . htmlspecialchars($ex->getMessage()) . "</pre>";
}

echo "<hr><p><strong>Instrucciones:</strong></p>";
echo "<ol><li>Si el log muestra 'Access denied' o 'Access denied for user', revisa DB_USERNAME/DB_PASSWORD en Render.</li>";
echo "<li>Si el log muestra 'getaddrinfo' o 'Unknown MySQL server host', revisa DB_HOST/DB_PORT en Render.</li>";
echo "<li>Si la conexion PDO falla aqui, actualiza las env vars en Render (Dashboard -> Environment).</li>";
echo "<li>Cuando soluciones, borra este archivo o limpia DEBUG_KEY para evitar exposicion.</li></ol>";
