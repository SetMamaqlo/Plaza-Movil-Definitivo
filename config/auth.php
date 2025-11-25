<?php
require_once __DIR__ . '/app.php';
require_once '../model/usermodel.php';
require_once '../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Identificadores de rol base
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 1);
}
if (!defined('ROLE_AGRICULTOR')) {
    define('ROLE_AGRICULTOR', 2);
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id_usuario'])) {
        header('Location: ' . base_url('view/login.php'));
        exit;
    }
}

function requireRole(array $allowedRoles): void
{
    requireLogin();
    $role = (int)($_SESSION['user_id_rol'] ?? 0);
    if (!in_array($role, $allowedRoles, true)) {
        header('Location: ' . base_url('index.php?error=unauthorized'));
        exit;
    }
}

function ensureAgricultor(): void
{
    requireRole([ROLE_AGRICULTOR]);
    if (empty($_SESSION['user_id_agricultor'])) {
        header('Location: ' . base_url('view/mis_productos.php?error=agricultor'));
        exit;
    }
}