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
if (!function_exists('detectBaseUrl')) {
    function detectBaseUrl(): string
    {
        $defaultLocal = 'http://localhost/Plaza-Movil-Definitivo';

        // 1) Honrar BASE_URL si viene configurada y no es la de ejemplo local
        $envBase = env('BASE_URL');
        if (!empty($envBase)) {
            $envBase = rtrim($envBase, '/');
            if ($envBase !== rtrim($defaultLocal, '/')) {
                return $envBase;
            }
        }

        // 2) Variables que expone Render al contenedor (docker)
        $renderUrl = env('RENDER_EXTERNAL_URL');
        if (empty($renderUrl) && env('RENDER_EXTERNAL_HOSTNAME')) {
            $renderUrl = 'https://' . env('RENDER_EXTERNAL_HOSTNAME');
        }
        if (!empty($renderUrl)) {
            return rtrim($renderUrl, '/');
        }

        // 3) Reconstruir con los encabezados de la peticion
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO']
            ?? $_SERVER['REQUEST_SCHEME']
            ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http');

        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $scriptDir = '';
            if (!empty($_SERVER['SCRIPT_NAME'])) {
                $dir = trim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                if ($dir && $dir !== '.') {
                    $scriptDir = '/' . $dir;
                }
            }
            return rtrim($scheme . '://' . $host . $scriptDir, '/');
        }

        // 4) Ultimo recurso: la ruta local usada en desarrollo
        return rtrim($defaultLocal, '/');
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', detectBaseUrl());
    // Compatibilidad con codigo antiguo que revisa $_SERVER
    $_SERVER['BASE_URL'] = BASE_URL;
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
