<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paleta padrão (portal público)
    |--------------------------------------------------------------------------
    | Espelha os fallbacks em App\Services\Portal\PortalThemeService.
    */

    'theme_defaults' => [
        'primary' => '#3b82f6',
        'secondary' => '#1e3a8a',
        'tertiary' => '#10b981',
    ],

    /*
    |--------------------------------------------------------------------------
    | Identidade Legiscola (plataforma) no portal público
    |--------------------------------------------------------------------------
    | Logo em public/ (ex.: img/legiscola.svg). Contato do proprietário/supporte.
    */

    'platform' => [
        'logo_path' => env('PLATFORM_LOGO_PATH', 'img/legiscola.svg'),
        'website_url' => env('PLATFORM_WEBSITE_URL'),
        'owner_label' => env('PLATFORM_OWNER_LABEL', 'Plataforma Legiscola'),
        'owner_email' => env('PLATFORM_OWNER_EMAIL'),
        'owner_phone' => env('PLATFORM_OWNER_PHONE'),
    ],

];
