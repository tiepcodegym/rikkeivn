<?php

return [
    'noti_env' => env('NOTI_ENV', 'local'),
    'refresh_minute' => env('NOTI_REFRESH', 5),
    'host' => env('NOTI_HOST', 'notifications.rikkei.vn'),
    'ws_host' => env('NOTI_WS_HOST', 'notifications.rikkei.vn'),
    'port' => env('NOTI_PORT', 3001),
    'protocol' => env('NOTI_PROTOCOL', 'wss'),
    'private_key' => env('NOTI_PRIVATE_KEY', 'S6Qdb8wgEgFCk7mCmE8bRlf5VDWjr9yV'),
    'token_valid_hour' => env('NOTI_TOKEN_VALID_HOUR', 2),
    'auth' => [
        'username' => env('NOTI_AUTH_USER', '4dm1nrkdn'),
        'password' => env('NOTI_AUTH_PASS', 'r1kk31s0ft192837465'),
    ],
    'api_uri' => env('NOTI_API_URI', '/api/notifications'),
];

