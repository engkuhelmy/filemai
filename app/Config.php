<?php

namespace App;

class Config
{
    private static ?array $envData = null;

    public static function load(): void
    {
        if (self::$envData !== null) {
            return;
        }

        self::$envData = [];
        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    self::$envData[$key] = $value;
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        $val = $_ENV[$key] ?? getenv($key);
        if ($val !== false && $val !== null && $val !== '') {
            return $val;
        }
        return self::$envData[$key] ?? $default;
    }

    public static function env(): string
    {
        return strtolower((string)self::get('APP_ENV', 'production'));
    }

    public static function isDemoMode(): bool
    {
        // 1. Explicit environment variable check
        $env = self::env();
        if (in_array($env, ['staging', 'demo', 'local', 'development'], true)) {
            return true;
        }

        // 2. Active if accessed under demo subdomain (e.g. demo.filemai.my)
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (str_starts_with($host, 'demo.') || str_contains($host, 'staging.')) {
            return true;
        }

        // 3. Demo switcher override via query param for private testing / pitch
        if (isset($_GET['demo']) && $_GET['demo'] === '1') {
            $_SESSION['demo_mode'] = true;
            return true;
        }
        if (!empty($_SESSION['demo_mode'])) {
            return true;
        }

        return false;
    }
}
