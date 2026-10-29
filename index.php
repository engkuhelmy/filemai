<?php
/**
 * Filemai.my - Platform Penstriman Mini Drama Menegak (MVP)
 * Dikuasakan oleh PHP 8.4, MariaDB & Nginx
 */

// If running under PHP built-in CLI server, serve static files directly or stream video with HTTP Range
if (php_sapi_name() === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $filePath = __DIR__ . $uriPath;
    if ($uriPath !== '/' && file_exists($filePath) && !is_dir($filePath)) {
        // Handle MP4 video with full HTTP Byte-Range support (required by iOS Safari & smooth seeking)
        if (str_ends_with($filePath, '.mp4')) {
            $fileSize = filesize($filePath);
            $file = fopen($filePath, 'rb');
            if (!$file) {
                http_response_code(404);
                exit;
            }

            $start = 0;
            $end = $fileSize - 1;

            header('Content-Type: video/mp4');
            header('Accept-Ranges: bytes');
            header('Cache-Control: public, max-age=86400');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: Range, Content-Type, Accept');
            header('Access-Control-Expose-Headers: Content-Range, Content-Length, Accept-Ranges');

            if (isset($_SERVER['HTTP_RANGE'])) {
                $range = $_SERVER['HTTP_RANGE'];
                if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                    $start = (int)$matches[1];
                    if (!empty($matches[2])) {
                        $end = (int)$matches[2];
                    }
                }

                if ($start > $end || $start >= $fileSize) {
                    http_response_code(416);
                    header("Content-Range: bytes */$fileSize");
                    fclose($file);
                    exit;
                }

                http_response_code(206);
                header("Content-Range: bytes $start-$end/$fileSize");
                $length = $end - $start + 1;
                header("Content-Length: $length");
            } else {
                header("Content-Length: $fileSize");
            }

            fseek($file, $start);
            $bufferSize = 64 * 1024;
            $bytesRemaining = $end - $start + 1;

            while (!feof($file) && $bytesRemaining > 0 && !connection_aborted()) {
                $bytesToRead = min($bufferSize, $bytesRemaining);
                $data = fread($file, $bytesToRead);
                if ($data === false) break;
                echo $data;
                flush();
                $bytesRemaining -= strlen($data);
            }

            fclose($file);
            exit;
        }

        return false;
    }
}

// Simple PSR-4 Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Router;
use App\Auth;
use App\Controllers\HomeController;
use App\Controllers\DramaController;
use App\Controllers\WatchController;
use App\Controllers\SubscriptionController;
use App\Controllers\CreatorController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ApiController;
use App\Controllers\PageController;

Auth::start();

// -------------------------------------------------------------
// Viewer Routes
// -------------------------------------------------------------
Router::get('/', [HomeController::class, 'index']);
Router::get('/drama/{slug}', [DramaController::class, 'show']);
Router::get('/drama/{slug}/episode/{number}', [WatchController::class, 'watchByDramaAndEpisode']);
Router::get('/watch/{id}', [WatchController::class, 'watch']);
Router::get('/subscribe', [SubscriptionController::class, 'index']);
Router::get('/subscribe/checkout', [SubscriptionController::class, 'checkout']);
Router::post('/subscribe/process', [SubscriptionController::class, 'process']);
Router::get('/subscribe/success', [SubscriptionController::class, 'success']);
Router::post('/webhook/payment', [SubscriptionController::class, 'webhook']);

// Corporate & Support Pages
Router::get('/terms', [PageController::class, 'terms']);
Router::get('/privacy', [PageController::class, 'privacy']);
Router::get('/contact', [PageController::class, 'contact']);
Router::post('/contact', [PageController::class, 'contact']);
Router::get('/faq', [PageController::class, 'faq']);

// -------------------------------------------------------------
// Auth & Demo Switcher Routes
// -------------------------------------------------------------
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/register', [AuthController::class, 'showRegister']);
Router::post('/register', [AuthController::class, 'register']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::get('/demo/switch/{id}', [AuthController::class, 'demoSwitch']);

// -------------------------------------------------------------
// Creator Studio Routes
// -------------------------------------------------------------
Router::get('/creator', [CreatorController::class, 'dashboard']);
Router::get('/creator/drama/new', [CreatorController::class, 'createDrama']);
Router::post('/creator/drama/store', [CreatorController::class, 'storeDrama']);
Router::get('/creator/drama/{id}', [CreatorController::class, 'manageDrama']);
Router::post('/creator/drama/{id}/episode/store', [CreatorController::class, 'storeEpisode']);
Router::get('/creator/episode/{id}/delete', [CreatorController::class, 'deleteEpisode']);
Router::get('/creator/drama/{id}/delete', [CreatorController::class, 'deleteDrama']);

// -------------------------------------------------------------
// Super Admin Routes
// -------------------------------------------------------------
Router::get('/admin', [AdminController::class, 'dashboard']);
Router::get('/admin/moderation', [AdminController::class, 'moderation']);
Router::post('/admin/drama/{id}/status', [AdminController::class, 'updateDramaStatus']);
Router::post('/admin/drama/{id}/feature', [AdminController::class, 'toggleFeatured']);
Router::get('/admin/ads', [AdminController::class, 'ads']);
Router::post('/admin/ad/store', [AdminController::class, 'storeAd']);
Router::post('/admin/ad/{id}/toggle', [AdminController::class, 'toggleAd']);
Router::get('/admin/ad/{id}/delete', [AdminController::class, 'deleteAd']);
Router::get('/admin/subscriptions', [AdminController::class, 'subscriptions']);
Router::get('/admin/users', [AdminController::class, 'users']);
Router::post('/admin/user/{id}/role', [AdminController::class, 'updateUserRole']);
Router::post('/admin/user/{id}/vip', [AdminController::class, 'toggleUserVip']);

// -------------------------------------------------------------
// API Endpoints
// -------------------------------------------------------------
Router::post('/api/episodes/{id}/view', [ApiController::class, 'recordView']);
Router::post('/api/ads/{id}/click', [ApiController::class, 'recordAdClick']);
Router::get('/api/ads/random', [ApiController::class, 'randomAd']);

// Dispatch Router
Router::dispatch();