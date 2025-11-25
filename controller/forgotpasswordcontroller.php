<?php

require_once '../config/conexion.php';
require_once '../model/usermodel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $model = new UserModel($pdo);

    if ($email && ($user = $model->getUserByEmail($email))) {
        $token = bin2hex(random_bytes(32));
        $hashed = hash('sha256', $token);
        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

        if ($model->storeResetToken($email, $hashed, $expiresAt)) {
            $resetLink = base_url('view/reset_password.php?token=' . urlencode($token));
            $subject = 'Recuperación de contraseña';
            $message = "Hola {$user['nombre_completo']},\n\nHaz solicitado recuperar tu contraseña. Ingresa al siguiente enlace antes de una hora:\n{$resetLink}\n\nSi no solicitaste este cambio, ignora este mensaje.";
            @mail($email, $subject, $message);
            error_log('Reset password enviado a ' . $email . ' con enlace: ' . $resetLink);
        }
    }

    header('Location: ' . base_url('view/forgot_password.php?success=1'));
    exit();
}

header('Location: ' . base_url('view/forgot_password.php'));
exit();