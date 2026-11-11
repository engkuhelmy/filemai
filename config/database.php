<?php

if (!function_exists('env')) {
    function env($key, $default = null) {
        $val = getenv($key);
        return $val !== false ? $val : $default;
    }
}

return [
    // Direct keys for legacy App\Database compatibility
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE', 'filemai'),
    'username' => env('DB_USERNAME', 'filemai_user'),
    'password' => env('DB_PASSWORD', 'Filemai2026Secure!'),
    'charset' => 'utf8mb4',

    // Laravel Eloquent / Database Manager compatibility
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'filemai'),
            'username' => env('DB_USERNAME', 'filemai_user'),
            'password' => env('DB_PASSWORD', 'Filemai2026Secure!'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
];
