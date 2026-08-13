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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    /*
    | Chave restrita só à Geocoding API no Google Cloud (evita cobrança de Maps JS, Places, etc.).
    | https://console.cloud.google.com/google/maps-apis
    */
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_GEOCODING_KEY'),
    ],

    /*
    | Nominatim (OpenStreetMap). Politica de uso publico: ~1 req/s, User-Agent identificavel, email.
    | https://operations.osmfoundation.org/policies/nominatim/
    */
    /*
    | Cloudflare Turnstile — https://dash.cloudflare.com/ → Turnstile → criar widget.
    | Local: use chaves de teste ou deixe vazio para desativar a verificação.
    */
    'turnstile' => [
        'key' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET_KEY'),
    ],

    'nominatim' => [
        'url' => env('NOMINATIM_URL', 'https://nominatim.openstreetmap.org'),
        'email' => env('NOMINATIM_EMAIL', env('MAIL_FROM_ADDRESS', '')),
        'user_agent' => env('NOMINATIM_USER_AGENT', 'DesenvolveCity/1.0 (geocoding)'),
        'min_request_interval_ms' => (int) env('NOMINATIM_MIN_REQUEST_INTERVAL_MS', 1100),
        'max_retries' => (int) env('NOMINATIM_MAX_RETRIES', 6),
    ],

];
