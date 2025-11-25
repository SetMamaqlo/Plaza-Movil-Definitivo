<?php
// Cargador sencillo de variables de entorno inspirado en vlucas/phpdotenv.
// Lee el archivo .env en la raíz del proyecto y expone valores a env(), BASE_URL y helpers.

if (!function_exists('loadEnv')) {
    function loadEnv(string $baseDir): void
    {
        $envPath = rtrim($baseDir, '/'). '/.env';
        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($name === '') {
                continue;
            }

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv($name . '=' . $value);
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
    }
}

// Cargar entorno una sola vez
if (!defined('APP_ENV_LOADED')) {
    loadEnv(dirname(__DIR__));
    define('APP_ENV_LOADED', true);
}

// Definir BASE_URL para construir rutas portables
if (!defined('BASE_URL')) {
    $rawBase = env('BASE_URL', 'http://pruevacodex-production.up.railway.app/');
    $normalized = rtrim($rawBase, '/');
    define('BASE_URL', $normalized);
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        return $path === '' ? BASE_URL : BASE_URL . '/' . $path;
    }
}

// Configuración de errores mínima: ocultar en producción, registrar en archivo
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/app.log');

if (env('APP_ENV', 'production') === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}