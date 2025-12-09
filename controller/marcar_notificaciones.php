<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/NotificacionModel.php';

if (!isset($_SESSION['user_id_usuario'])) {
    header('Location: ' . base_url('view/login.php'));
    exit;
}

$noti = new NotificacionModel($pdo);
$noti->marcarTodasLeidas((int)$_SESSION['user_id_usuario']);

$referer = $_SERVER['HTTP_REFERER'] ?? base_url('index.php');
header('Location: ' . $referer);
exit;
