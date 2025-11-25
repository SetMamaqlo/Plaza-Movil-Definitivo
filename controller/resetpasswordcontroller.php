<?php

require_once '../config/conexion.php';
require_once '../model/usermodel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm || strlen($password) < 8) {
        header('Location: ' . base_url('view/reset_password.php?token=' . urlencode($token) . '&error=1'));
        exit();
    }

    $tokenHash = hash('sha256', $token);
    $model = new UserModel($pdo);
    $reset = $model->getResetRequest($tokenHash);

    if (!$reset) {
        header('Location: ' . base_url('view/reset_password.php?error=invalid'));
        exit();
    }

    $expires = new DateTime($reset['expires_at']);
    if ($expires < new DateTime()) {
        $model->deleteResetToken($tokenHash);
        header('Location: ' . base_url('view/reset_password.php?error=expired'));
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if ($model->updatePasswordByEmail($reset['email'], $hashedPassword)) {
        $model->deleteResetToken($tokenHash);
        header('Location: ' . base_url('view/reset_password.php?success=1'));
        exit();
    }

    header('Location: ' . base_url('view/reset_password.php?error=save'));
    exit();
}

header('Location: ' . base_url('view/reset_password.php'));
exit();
