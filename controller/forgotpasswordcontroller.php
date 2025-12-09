<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../model/usermodel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Envía correo de recuperación usando PHPMailer (SMTP recomendado).
 * Requiere variables de entorno:
 * MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION (tls|ssl|none),
 * MAIL_FROM_ADDRESS, MAIL_FROM_NAME
 */
function sendResetEmail(string $to, string $toName, string $resetLink): bool
{
    $host = env('MAIL_HOST');
    $port = (int) env('MAIL_PORT', 587);
    $user = env('MAIL_USERNAME');
    $pass = env('MAIL_PASSWORD');
    $encryption = strtolower(env('MAIL_ENCRYPTION', 'tls'));
    $from = env('MAIL_FROM_ADDRESS') ?: $user;
    $fromName = env('MAIL_FROM_NAME', 'Plaza Movil');

    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = $port;
        if ($encryption === 'ssl') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mailer->SMTPAuth = true;
        $mailer->Username = $user;
        $mailer->Password = $pass;
        $mailer->CharSet = 'UTF-8';

        $mailer->setFrom($from, $fromName);
        $mailer->addAddress($to, $toName);
        $mailer->Subject = 'Recuperacion de contrasena';

        $plain = "Hola {$toName},\n\nHas solicitado recuperar tu contrasena. Ingresa al siguiente enlace antes de una hora:\n{$resetLink}\n\nSi no solicitaste este cambio, ignora este mensaje.";
        $html = "<p>Hola {$toName},</p><p>Has solicitado recuperar tu contraseña. Ingresa al siguiente enlace antes de una hora:</p><p><a href=\"{$resetLink}\">Recuperar contraseña</a></p><p>Si no solicitaste este cambio, ignora este mensaje.</p>";

        $mailer->isHTML(true);
        $mailer->Body = $html;
        $mailer->AltBody = $plain;

        $mailer->send();
        return true;
    } catch (MailException $e) {
        error_log('PHPMailer error: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $model = new UserModel($pdo);

    if ($email && ($user = $model->getUserByEmail($email))) {
        $token = bin2hex(random_bytes(32));
        $hashed = hash('sha256', $token);
        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

        if ($model->storeResetToken($email, $hashed, $expiresAt)) {
            $resetLink = base_url('view/reset_password.php?token=' . urlencode($token));
            $sent = sendResetEmail($email, $user['nombre_completo'], $resetLink);
            if (!$sent) {
                $logDir = __DIR__ . '/../storage/logs';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0777, true);
                }
                file_put_contents($logDir . '/password_reset.log', '[' . date('c') . "] {$email} => {$resetLink}\n", FILE_APPEND);
            }
        }
    }

    header('Location: ' . base_url('view/forgot_password.php?success=1'));
    exit();
}

header('Location: ' . base_url('view/forgot_password.php'));
exit();
