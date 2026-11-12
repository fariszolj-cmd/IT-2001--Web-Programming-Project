<?php

return [
    'host' => '127.0.0.1',
    'database' => 'fariscommerce',
    'username' => 'your_mysql_username',
    'password' => 'your_mysql_password',
    'charset' => 'utf8mb4',
    'api_tokens' => [
        'admin' => 'replace-with-a-long-random-admin-token',
        'reader' => 'replace-with-a-long-random-read-only-token',
    ],
    'external_api' => [
        'timeout_seconds' => 5,
        'dummyjson_base_url' => 'https://dummyjson.com',
        'olx_base_url' => 'https://api.olx.ba',
        'olx_api_token' => '',
    ],
];