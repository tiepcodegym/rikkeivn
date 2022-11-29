<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => Rikkei\Intranet\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'google' => [
        'api_key' => env('GOOGLE_API_KEY', 'AIzaSyB3qj6B6VFx7DsMsOuYmjUQtj7u78Ujecg'),
        'client_id'     => env('GOOGLE_ID'),
        'client_secret' => env('GOOGLE_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT')
    ],

    'account_root' => env('ACCOUNT_ROOT'),

    'file' => [
        'image_allow' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
        ],
        'image_max' => '2048', //kB
        'audio_allow' => [
            'audio/mp3',
            'audio/mpeg'
        ],
        'audio_max' => '20971520', //kb
        'cv_allow' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/pdf',
            'application/msword',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ],
        'cv_import_allow' => [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'cv_max' => '5120',
        'cv_import' => [
            'xls',
            'xlsx',
        ],
    ],
    'curl' => [
        'server_webvn' => env('SERVER_WEBVN'),
        'server_recruitment' => env('SERVER_RECRUITMENT'),
    ],
    'webvn_api' => [
        'base_url' => env('SERVER_WEBVN', 'https://vn.rikkeisoft.com'),
        'url_get_article_request' => env('URL_WEBVN_GET_ARTICLE_REQUEST', '/recruitment/request/article'),
    ],
    'captcha' => [
        'secret_key' => env('GOOGLE_CAPTCHA_SECRET_KEY'),
    ],
    'webvn' => env('SERVER_WEBVN'),
    'training' => env('SERVER_TRAINING','https://training.rikkei.vn'),
    'core_values_url' => env('SERVER_CORE_VALUES','https://corevalues.rikkeisoft.com'),
    'tuyen_dung_url' => env('TUYEN_DUNG_URL','https://tuyendung.rikkeisoft.com'),
    'hrm_url' => env('SERVER_HRM','https://hrm.rikkei.vn'),
    'mentorship_url' => env('MENTORSHIP_URL','https://mentorship.rikkei.vn'),
    'firebase' => [
        'url' => env('GOOGLE_FIREBASE_NOTIFICATION_URL'),
        'key' => env('LEGACY_SERVER_KEY_FIREBASE')
    ],
    'point' => [
        'add' => env('REWARD_USER_POINT_API', 'http://172.16.14.32:81/api/v1/add-point')
    ],
    'home_message' => [
        'reset_cache' => env('HOME_MESSAGE_CACHE_API', 'https://mobile.rikkei.vn/api/v1/home-message/reset-cache')
    ],
    'rikkei_10years_url' => env('RIKKEI_10YEARS_URL','https://10namcungnhau.rikkeisoft.com/'),
    'roadtojapan_url' => env('ROADTOJAPAN_URL','https://roadtojapan.rikkeisoft.com/'),
    'hrm_profile_url' => env('HRM_PROFILE_URL','https://hrm.rikkei.vn/hrm/profile/general'),
    'rikkei_10years_1_news_url' => env('RIKKEI_10YEARS_1_NEWS_URL','https://10namcungnhau.rikkeisoft.com/news/post/'),
    'rikkei_10years_news_url' => env('RIKKEI_10YEARS_NEWS_URL','https://10namcungnhau.rikkeisoft.com/news'),
];
