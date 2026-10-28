<?php

namespace App\Controllers;

use App\Database;
use App\Auth;
use App\Router;

class AdminController
{
    public function __construct()
    {
        Auth::requireRole('admin');
    }

    public function dashboard(): void
    {
        $kpis = [
            'total_users' => Database::fetchOne("SELECT COUNT(*) as c FROM users")['c'] ?? 0,
            'total_creators' => Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'creator'")['c'] ?? 0,
            'total_dramas' => Database::fetchOne("SELECT COUNT(*) as c FROM dramas")['c'] ?? 0,
            'total_episodes' => Database::fetchOne("SELECT COUNT(*) as c FROM episodes")['c'] ?? 0,
            'total_views' => Database::fetchOne("SELECT COALESCE(SUM(views_count), 0) as c FROM dramas")['c'] ?? 0,
            'total_revenue' => Database::fetchOne("SELECT COALESCE(SUM(amount), 0) as c FROM subscriptions WHERE status = 'success'")['c'] ?? 0,
            'pending_dramas' => Database::fetchOne("SELECT COUNT(*) as c FROM dramas WHERE status = 'pending'")['c'] ?? 0,
        ];

        // Recent Dramas needing review
        $pendingList = Database::fetchAll(
            "SELECT d.*, u.name as creator_name,
             (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as episode_count
             FROM dramas d
             JOIN users u ON d.creator_id = u.id
             WHERE d.status = 'pending'
             ORDER BY d.created_at DESC"
        );

        // Recent Subscriptions
        $recentSubs = Database::fetchAll(
            "SELECT s.*, u.name as user_name, u.email as user_email
             FROM subscriptions s
             JOIN users u ON s.user_id = u.id
             ORDER BY s.created_at DESC LIMIT 5"
        );

        Router::render('admin/dashboard', [
            'title' => 'Super Admin Panel - Filemai.my',
            'kpis' => $kpis,
            'pendingList' => $pendingList,
            'recentSubs' => $recentSubs,
        ]);
    }

    public function moderation(): void
    {
        $dramas = Database::fetchAll(
            "SELECT d.*, u.name as creator_name,
             (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as episode_count
             FROM dramas d
             JOIN users u ON d.creator_id = u.id
             ORDER BY FIELD(d.status, 'pending', 'published', 'rejected'), d.created_at DESC"
        );

        Router::render('admin/moderation', [
            'title' => 'Moderasi Kandungan & Kelulusan Video',
            'dramas' => $dramas,
        ]);
    }

    public function updateDramaStatus(string $id): void
    {
        $status = $_POST['status'] ?? 'published';
        if (in_array($status, ['published', 'pending', 'rejected'])) {
            Database::execute("UPDATE dramas SET status = ? WHERE id = ?", [$status, $id]);
            $_SESSION['success'] = "Status drama telah dikemas kini kepada '$status'.";
        }
        Router::redirect($_SERVER['HTTP_REFERER'] ?? '/admin/moderation');
    }

    public function toggleFeatured(string $id): void
    {
        $drama = Database::fetchOne("SELECT featured FROM dramas WHERE id = ?", [$id]);
        if ($drama) {
            $newVal = $drama['featured'] ? 0 : 1;
            Database::execute("UPDATE dramas SET featured = ? WHERE id = ?", [$newVal, $id]);
            $_SESSION['success'] = $newVal ? "Drama disematkan sebagai Featured di laman utama." : "Drama dibuang daripada Featured.";
        }
        Router::redirect($_SERVER['HTTP_REFERER'] ?? '/admin/moderation');
    }

    public function ads(): void
    {
        $ads = Database::fetchAll("SELECT * FROM ads ORDER BY created_at DESC");

        Router::render('admin/ads', [
            'title' => 'Pengurusan Iklan Interstitial Platform',
            'ads' => $ads,
        ]);
    }

    public function storeAd(): void
    {
        $title = trim($_POST['title'] ?? '');
        $sponsor = trim($_POST['sponsor_name'] ?? '');
        $duration = max(5, min(60, (int)($_POST['duration_seconds'] ?? 15)));
        $clickUrl = trim($_POST['click_url'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $videoUrl = '/uploads/videos/sample_reel.mp4';
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'webm'])) {
                $filename = 'ad_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = __DIR__ . '/../../uploads/videos/' . $filename;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest)) {
                    $videoUrl = '/uploads/videos/' . $filename;
                }
            }
        } elseif (!empty($_POST['video_url'])) {
            $videoUrl = trim($_POST['video_url']);
        }

        Database::insertGetId(
            "INSERT INTO ads (title, video_url, duration_seconds, click_url, sponsor_name, is_active)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$title, $videoUrl, $duration, $clickUrl, $sponsor, $isActive]
        );

        $_SESSION['success'] = "Iklan '$title' berjaya ditambah!";
        Router::redirect('/admin/ads');
    }

    public function toggleAd(string $id): void
    {
        $ad = Database::fetchOne("SELECT is_active FROM ads WHERE id = ?", [$id]);
        if ($ad) {
            $newVal = $ad['is_active'] ? 0 : 1;
            Database::execute("UPDATE ads SET is_active = ? WHERE id = ?", [$newVal, $id]);
            $_SESSION['success'] = "Status iklan dikemas kini.";
        }
        Router::redirect('/admin/ads');
    }

    public function deleteAd(string $id): void
    {
        Database::execute("DELETE FROM ads WHERE id = ?", [$id]);
        $_SESSION['success'] = "Iklan berjaya dipadam.";
        Router::redirect('/admin/ads');
    }

    public function subscriptions(): void
    {
        $subscriptions = Database::fetchAll(
            "SELECT s.*, u.name as user_name, u.email as user_email
             FROM subscriptions s
             JOIN users u ON s.user_id = u.id
             ORDER BY s.created_at DESC"
        );

        $totalRevenue = Database::fetchOne("SELECT COALESCE(SUM(amount), 0) as rev FROM subscriptions WHERE status = 'success'")['rev'] ?? 0;

        Router::render('admin/subscriptions', [
            'title' => 'Rekod Transaksi Langganan VIP',
            'subscriptions' => $subscriptions,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    public function users(): void
    {
        $users = Database::fetchAll(
            "SELECT u.*, 
             (SELECT COUNT(*) FROM dramas WHERE creator_id = u.id) as drama_count
             FROM users u
             ORDER BY u.id ASC"
        );

        Router::render('admin/users', [
            'title' => 'Pengurusan Pengguna & Peranan',
            'users' => $users,
        ]);
    }

    public function updateUserRole(string $userId): void
    {
        $role = $_POST['role'] ?? 'viewer';
        if (in_array($role, ['admin', 'creator', 'viewer'])) {
            Database::execute("UPDATE users SET role = ? WHERE id = ?", [$role, $userId]);
            $_SESSION['success'] = "Peranan pengguna berjaya ditukar.";
        }
        Router::redirect('/admin/users');
    }

    public function toggleUserVip(string $userId): void
    {
        $user = Database::fetchOne("SELECT subscription_status FROM users WHERE id = ?", [$userId]);
        if ($user) {
            if ($user['subscription_status'] === 'premium') {
                Database::execute("UPDATE users SET subscription_status = 'free', subscription_expires_at = NULL WHERE id = ?", [$userId]);
                $_SESSION['success'] = "Status VIP ditarik balik.";
            } else {
                Database::execute("UPDATE users SET subscription_status = 'premium', subscription_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?", [$userId]);
                $_SESSION['success'] = "Status VIP 30 hari diberikan secara manual.";
            }
        }
        Router::redirect('/admin/users');
    }
}
