<?php

return [
    //Token connect api subscriber_notify
    'token' => env('TOKEN_SUBSCRIBER_NOTIFY'),
    'sync_base_url' => [
        'employee' => env('SYNC_BASE_API_EMPLOYEE')
    ],
    'rikkei_app_backend' => [
        'BASE_HOST' => env('RIKKEI_API_HOST', 'http://172.16.14.32:81'),
        'SECRET' => env('RIKKEI_API_SECRET', 'rikkei.mobile.sd'),
        'grateful' => env('GRATEFUL_URL', 'https://mobile.rikkei.vn/grateful')
    ],
    'pdf_cv_generate' => env('API_CV_PARSER', 'https://cv-parser.rikkei.org/resume-parser'),
    'token_api_hrm' => env('TOKEN_API_HRM'),
];