<?php
// Database configuration for MariaDB

return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'filemai',
    'username' => 'filemai',
    'password' => 'cxA2t051ZEmUOPM0',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
