<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pausa entre disparos de e-mail (microssegundos)
    |--------------------------------------------------------------------------
    |
    | Reduz picos ao enfileirar muitos Mailables. O envio real usa MAIL_* do .env.
    |
    */
    'email_delay_microseconds' => (int) env('ANNOUNCEMENT_EMAIL_DELAY_US', 200_000),

    /*
    |--------------------------------------------------------------------------
    | Prefixo SMS (simulado)
    |--------------------------------------------------------------------------
    |
    | Texto curto antes do corpo. O texto total é truncado para caber em SMS.
    |
    */
    'sms_prefix_max_chars' => (int) env('ANNOUNCEMENT_SMS_PREFIX_MAX', 36),

    'sms_body_max_chars' => (int) env('ANNOUNCEMENT_SMS_BODY_MAX', 280),
];
