<?php

namespace App\Controllers;

use App\Database;
use App\Auth;
use App\Router;

class CreatorController
{
    public function __construct()
    {
        Auth::requireRole(['creator', 'admin']);
    }

    public function dashboard(): void
    {
        $userId = Auth::id();

        // Get creator's dramas
        $dramas = Database::fetchAll(
            "SELECT d.*, 
             (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as episode_count,
             (SELECT COALESCE(SUM(views_count), 0) FROM episodes WHERE drama_id = d.id) as total_ep_views
             FROM dramas d
             WHERE d.creator_id = ?
             ORDER BY d.created_at DESC",
            [$userId]
        );

        // Stats
        $totalViews = 0;
        $totalEpisodes = 0;
        foreach ($dramas as $d) {
            $totalViews += (int)$d['views_count'];
            $totalEpisodes += (int)$d['episode_count'];
        }

        Router::render('creator/dashboard', [
            'title' => 'Creator Studio - Pengurusan Drama & Episod',
            'dramas' => $dramas,
            'totalViews' => $totalViews,
            'totalEpisodes' => $totalEpisodes,
            'dramaCount' => count($dramas),
        ]);
    }

    public function createDrama(): void
    {
        Router::render('creator/create_drama', [
            'title' => 'Cipta Siri / Filem Baru - Creator Studio',
        ]);
    }

    public function storeDrama(): void
    {
        $userId = Auth::id();
        $title = trim($_POST['title'] ?? '');
        $genre = trim($_POST['genre'] ?? 'Romance');
        $type = in_array($_POST['type'] ?? '', ['series', 'movie']) ? $_POST['type'] : 'series';
        $synopsis = trim($_POST['synopsis'] ?? '');

        if (empty($title)) {
            $_SESSION['error'] = 'Tajuk drama wajib diisi!';
            Router::redirect('/creator/drama/new');
            return;
        }

        // Generate unique slug
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $slug = $baseSlug;
        $i = 1;
        while (Database::fetchOne("SELECT id FROM dramas WHERE slug = ?", [$slug])) {
            $slug = $baseSlug . '-' . $i++;
        }

        // Handle Poster upload or URL
        $posterUrl = '/uploads/posters/jodoh-kontrak-ceo.jpg'; // default fallback
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'poster_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = __DIR__ . '/../../uploads/posters/' . $filename;
                if (move_uploaded_file($_FILES['poster']['tmp_name'], $dest)) {
                    $posterUrl = '/uploads/posters/' . $filename;
                }
            }
        } elseif (!empty($_POST['poster_url'])) {
            $posterUrl = trim($_POST['poster_url']);
        }

        // Insert drama (initial status is 'pending' for admin review, or 'published' if creator is admin)
        $status = (Auth::role() === 'admin') ? 'published' : 'pending';

        $dramaId = Database::insertGetId(
            "INSERT INTO dramas (creator_id, title, slug, synopsis, poster_url, genre, type, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$userId, $title, $slug, $synopsis, $posterUrl, $genre, $type, $status]
        );

        $_SESSION['success'] = "Drama '$title' berjaya dicipta! Sila muat naik episod pertama.";
        Router::redirect('/creator/drama/' . $dramaId);
    }

    public function manageDrama(string $id): void
    {
        $userId = Auth::id();
        $drama = Database::fetchOne("SELECT * FROM dramas WHERE id = ? AND (creator_id = ? OR ? = 'admin')", [$id, $userId, Auth::role()]);

        if (!$drama) {
            http_response_code(404);
            Router::render('errors/404', ['title' => 'Drama Tidak Ditemui']);
            return;
        }

        $episodes = Database::fetchAll("SELECT * FROM episodes WHERE drama_id = ? ORDER BY episode_number ASC", [$drama['id']]);

        Router::render('creator/manage_drama', [
            'title' => 'Urus Episod: ' . $drama['title'],
            'drama' => $drama,
            'episodes' => $episodes,
        ]);
    }

    public function storeEpisode(string $dramaId): void
    {
        $userId = Auth::id();
        $drama = Database::fetchOne("SELECT * FROM dramas WHERE id = ? AND (creator_id = ? OR ? = 'admin')", [$dramaId, $userId, Auth::role()]);

        if (!$drama) {
            http_response_code(403);
            die("Tiada kebenaran untuk menambah episod.");
        }

        $epNumber = (int)($_POST['episode_number'] ?? 1);
        $title = trim($_POST['title'] ?? "Episod $epNumber");
        $duration = (int)($_POST['duration'] ?? 60);
        $isFree = isset($_POST['is_free_preview']) ? 1 : 0;

        // Video upload or external URL
        $videoUrl = '/uploads/videos/sample_reel.mp4';
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'webm', 'mov', 'm4v'])) {
                $filename = 'ep_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = __DIR__ . '/../../uploads/videos/' . $filename;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest)) {
                    $videoUrl = '/uploads/videos/' . $filename;
                }
            }
        } elseif (!empty($_POST['video_url'])) {
            $videoUrl = trim($_POST['video_url']);
        }

        Database::insertGetId(
            "INSERT INTO episodes (drama_id, episode_number, title, video_url, duration, is_free_preview)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$drama['id'], $epNumber, $title, $videoUrl, $duration, $isFree]
        );

        $_SESSION['success'] = "Episod $epNumber berjaya dimuat naik!";
        Router::redirect('/creator/drama/' . $drama['id']);
    }

    public function deleteEpisode(string $id): void
    {
        $userId = Auth::id();
        $ep = Database::fetchOne(
            "SELECT e.*, d.creator_id FROM episodes e JOIN dramas d ON e.drama_id = d.id WHERE e.id = ?",
            [$id]
        );

        if ($ep && ($ep['creator_id'] == $userId || Auth::role() === 'admin')) {
            Database::execute("DELETE FROM episodes WHERE id = ?", [$id]);
            $_SESSION['success'] = "Episod berjaya dipadam.";
            Router::redirect('/creator/drama/' . $ep['drama_id']);
            return;
        }

        http_response_code(403);
        die("Tiada kebenaran memadam episod.");
    }

    public function deleteDrama(string $id): void
    {
        $userId = Auth::id();
        $drama = Database::fetchOne("SELECT * FROM dramas WHERE id = ? AND (creator_id = ? OR ? = 'admin')", [$id, $userId, Auth::role()]);

        if ($drama) {
            Database::execute("DELETE FROM dramas WHERE id = ?", [$id]);
            $_SESSION['success'] = "Drama berjaya dipadam.";
        }

        Router::redirect('/creator');
    }
}
