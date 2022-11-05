<?php

/* ------------------------------------------------------------------- */
/*  Swilen configuration rules */
/* ------------------------------------------------------------------- */

return [
    // ----------------------------------------------------------------
    // | BASE APPLICATION CONFIG
    // ----------------------------------------------------------------
    'app' => [
        // APPLICATION SECRET KEY
        'secret' => env('APP_SECRET', ''),

        // APPLICATION ENVIRONMENT
        'env' => env('APP_ENV', 'production'),
    ],

    // ----------------------------------------------------------------
    // | DATABASE CONNECTION CONFIG
    // ----------------------------------------------------------------
    'database' => [
        'driver' => env('DB_DRIVER'),
        'host' => env('DB_HOST'),
        'schema' => env('DB_SCHEMA'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
    ],

    // ----------------------------------------------------------------
    // | CORS CONFIG
    // ----------------------------------------------------------------
    'cors' => [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method',
        'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE',
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Max-Age' => 86400,
        'Allow' => 'GET, POST, OPTIONS, PUT, DELETE',
    ],

    // ----------------------------------------------------------------
    // | MAILERS LIST AND CONFIGS
    // ----------------------------------------------------------------
    'mailers' => [
        'default' => 'sendmail',

        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -t -i'),
        ],
    ],

    'providers' => [
    ],
];
