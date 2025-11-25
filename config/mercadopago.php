<?php
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../vendor/autoload.php';
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;


function mpClient(): PreferenceClient
{
    $token = env('MERCADOPAGO_ACCESS_TOKEN');
    if (empty($token)) {
        throw new RuntimeException('Falta MERCADOPAGO_ACCESS_TOKEN en el entorno.');
    }
    MercadoPagoConfig::setAccessToken($token);
    return new PreferenceClient();
}

function buildBackUrls(string $path): array
{
    $base = base_url($path);
    $query = '?status=%s&payment_id={payment.id}&preference_id={preference.id}';
    return [
        'success' => $base . sprintf($query, 'success'),
        'failure' => $base . sprintf($query, 'failure'),
        'pending' => $base . sprintf($query, 'pending'),
    ];
}