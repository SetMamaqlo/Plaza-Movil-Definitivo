<?php

require_once '../config/conexion.php';
require_once '../model/usermodel.php';
require_once '../config/app.php';

/**
 * Envío simple por SMTP (o mail() como respaldo).
 * Requiere en .env:
 * MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD,
 * MAIL_ENCRYPTION (tls|ssl|none), MAIL_FROM_ADDRESS, MAIL_FROM_NAME
 */
function sendResetEmail(string $to, string $toName, string $resetLink): bool
{
    $host = env('MAIL_HOST');
    $port = (int) env('MAIL_PORT', 587);
    $user = env('MAIL_USERNAME');
    $pass = env('MAIL_PASSWORD');
    $encryption = strtolower(env('MAIL_ENCRYPTION', 'tls'));
    $from = env('MAIL_FROM_ADDRESS', 'no-reply@example.com');
    $fromName = env('MAIL_FROM_NAME', 'Plaza Móvil');

    $subject = 'Recuperación de contraseña';
    $body = "Hola {$toName},\n\nHas solicitado recuperar tu contraseña. Ingresa al siguiente enlace antes de una hora:\n{$resetLink}\n\nSi no solicitaste este cambio, ignora este mensaje.";

    // Si no hay SMTP configurado, intentar mail() como respaldo
    if (!$host || !$user || !$pass) {
        $headers = "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $ok = @mail($to, $subject, $body, $headers);
        if (!$ok) {
            error_log('No se pudo enviar correo de recuperación con mail()');
        }
        return $ok;
    }

    $secureHost = $host;
    if ($encryption === 'ssl') {
        $secureHost = 'ssl://' . $host;
    }

    $errno = $errstr = null;
    $fp = @fsockopen($secureHost, $port, $errno, $errstr, 20);
    if (!$fp) {
        error_log("SMTP: no se pudo conectar ({$errstr})");
        return false;
    }
    stream_set_timeout($fp, 20);

    $read = function() use ($fp) {
        $data = '';
        while ($line = fgets($fp, 515)) {
            $data .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $data;
    };

    $write = function(string $cmd) use ($fp) {
        fwrite($fp, $cmd . "\r\n");
    };

    $expect = function(string $expected) use ($read) {
        $resp = $read();
        return str_starts_with($resp, $expected);
    };

    $read(); // banner
    $write('EHLO plazamovil.local');
    if (!$expect('250')) {
        fclose($fp);
        return false;
    }

    if ($encryption === 'tls') {
        $write('STARTTLS');
        if (!$expect('220') || !stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp);
            error_log('SMTP: fallo STARTTLS');
            return false;
        }
        $write('EHLO plazamovil.local');
        if (!$expect('250')) {
            fclose($fp);
            return false;
        }
    }

    $write('AUTH LOGIN');
    if (!$expect('334')) {
        fclose($fp);
        return false;
    }
    $write(base64_encode($user));
    if (!$expect('334')) {
        fclose($fp);
        return false;
    }
    $write(base64_encode($pass));
    if (!$expect('235')) {
        fclose($fp);
        error_log('SMTP: credenciales incorrectas');
        return false;
    }

    $fromHeader = '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>';
    $toHeader = '=?UTF-8?B?' . base64_encode($toName) . '?= <' . $to . '>';
    $headers = "From: {$fromHeader}\r\n";
    $headers .= "To: {$toHeader}\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    $write('MAIL FROM:<' . $from . '>');
    if (!$expect('250')) {
        fclose($fp);
        return false;
    }

    $write('RCPT TO:<' . $to . '>');
    if (!$expect('250')) {
        fclose($fp);
        return false;
    }

    $write('DATA');
    if (!$expect('354')) {
        fclose($fp);
        return false;
    }

    $message = $headers . "\r\n" . $body . "\r\n.";
    $write($message);
    if (!$expect('250')) {
        fclose($fp);
        return false;
    }

    $write('QUIT');
    fclose($fp);
    return true;
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
            if (!sendResetEmail($email, $user['nombre_completo'], $resetLink)) {
                error_log('No se pudo enviar el correo de recuperación a ' . $email);
            }
        }
    }

    header('Location: ' . base_url('view/forgot_password.php?success=1'));
    exit();
}

header('Location: ' . base_url('view/forgot_password.php'));
exit();
