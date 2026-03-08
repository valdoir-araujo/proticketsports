<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // 🟢 AJUSTE DO MERCADO PAGO
    // A chave foi alterada de 'token' para 'access_token' para bater com o Controller
    'mercadopago' => [
        'public_key'    => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token'  => env('MERCADOPAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'), // Assinatura em Suas integrações > Webhooks
    ],
    
    // Configuração para definir qual Gateway é o padrão
    'payment' => [
        'default_gateway' => env('PAYMENT_GATEWAY', 'mercadopago'),
    ],
    
    // 🟢 CONFIGURAÇÃO DO STRAVA (cada utilizador conecta o próprio Strava no perfil)
    'strava' => [
        'client_id' => env('STRAVA_CLIENT_ID'),
        'client_secret' => env('STRAVA_CLIENT_SECRET'),
        // Opcional: se vazio, usa APP_URL + /strava/callback. No painel Strava use o mesmo domínio.
        'redirect_uri' => env('STRAVA_REDIRECT_URI'),
    ],

];