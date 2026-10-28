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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        $routesForMethod = self::$routes[$method] ?? [];

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
}
