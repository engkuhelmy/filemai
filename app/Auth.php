<?php

namespace App;

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [self::id()]);
        if (!$user) {
            self::logout();
            return null;
        }

        // Check if subscription has expired
        if ($user['subscription_status'] === 'premium' && !empty($user['subscription_expires_at'])) {
            if (strtotime($user['subscription_expires_at']) < time()) {
                Database::execute("UPDATE users SET subscription_status = 'free' WHERE id = ?", [$user['id']]);
                $user['subscription_status'] = 'free';
            }
        }

        return $user;
    }

    public static function role(): string
    {
        $user = self::user();
        return $user['role'] ?? 'guest';
    }

    public static function isPremium(): bool
    {
        $user = self::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true; // Admins get VIP access
        if ($user['subscription_status'] !== 'premium') return false;
        if (empty($user['subscription_expires_at'])) return true;
        return strtotime($user['subscription_expires_at']) >= time();
    }

    public static function login(string $email, string $password): bool
    {
        self::start();
        $user = Database::fetchOne("SELECT * FROM users WHERE email = ?", [trim(strtolower($email))]);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    }

    public static function loginById(int $userId): bool
    {
        self::start();
        $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    }

    public static function register(string $name, string $email, string $password, string $role = 'viewer'): int|false
    {
        self::start();
        $exists = Database::fetchOne("SELECT id FROM users WHERE email = ?", [trim(strtolower($email))]);
        if ($exists) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $id = Database::insertGetId(
            "INSERT INTO users (name, email, password, role, subscription_status) VALUES (?, ?, ?, ?, 'free')",
            [trim($name), trim(strtolower($email)), $hash, in_array($role, ['admin', 'creator', 'viewer']) ? $role : 'viewer']
        );

        if ($id) {
            self::loginById($id);
            return $id;
        }
        return false;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(array|string $roles): void
    {
        self::requireAuth();
        $roles = is_array($roles) ? $roles : [$roles];
        $currentRole = self::role();
        if (!in_array($currentRole, $roles) && $currentRole !== 'admin') {
            http_response_code(403);
            die("Akses Disekat: Peranan anda ($currentRole) tidak mempunyai kebenaran untuk membuka halaman ini.");
        }
    }
}
