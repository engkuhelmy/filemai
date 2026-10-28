<?php

namespace App;

class Router
{
    private static array $routes = [];

    public static function get(string $path, callable|array $handler): void
    {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, callable|array $handler): void
    {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(): void
    {
        self::enforceHttps();

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'HEAD') {
            $routesForMethod = self::$routes['GET'] ?? [];
        } else {
            $routesForMethod = self::$routes[$method] ?? [];
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        foreach ($routesForMethod as $routePath => $handler) {
            $pattern = self::convertRouteToRegex($routePath);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Call handler
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $action], $matches);
                } else {
                    call_user_func_array($handler, $matches);
                }
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        self::render('errors/404', ['title' => 'Halaman Tidak Ditemui']);
    }

    private static function convertRouteToRegex(string $route): string
    {
        if ($route === '/') {
            return '#^/$#';
        }
        $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    public static function render(string $view, array $data = []): void
    {
        extract($data);
        $user = Auth::user();
        $isPremium = Auth::isPremium();
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die("View [$view] tidak ditemui di $viewPath");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // If the view handles its own layout, just echo it
        if (isset($noLayout) && $noLayout) {
            echo $content;
            return;
        }

        // Default layout
        require __DIR__ . '/../views/layouts/app.php';
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public static function enforceHttps(): void
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Skip enforcement on local IP / CLI development
        if (empty($host) || str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            return;
        }

        // Detect if request is HTTPS
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (isset($_SERVER['HTTP_CF_VISITOR']) && str_contains($_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"'))
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        if (!$isHttps) {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $secureUrl = 'https://' . $host . $uri;
            header("Location: $secureUrl", true, 301);
            exit;
        }

        // Send HSTS header on secure connections
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
