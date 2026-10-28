<?php

namespace App\Controllers;

use App\Database;
use App\Router;

class HomeController
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $genre = trim($_GET['genre'] ?? '');
        $tab = trim($_GET['tab'] ?? 'trends');

        // All published dramas with creator details and episode count
        $sql = "SELECT d.*, u.name as creator_name,
                (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as episode_count,
                (SELECT id FROM episodes WHERE drama_id = d.id ORDER BY episode_number ASC LIMIT 1) as first_episode_id
                FROM dramas d
                JOIN users u ON d.creator_id = u.id
                WHERE d.status = 'published'";
        
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (d.title LIKE ? OR d.synopsis LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($genre) && $genre !== 'Semua') {
            $sql .= " AND d.genre = ?";
            $params[] = $genre;
        }

        $sql .= " ORDER BY d.views_count DESC";
        $dramas = Database::fetchAll($sql, $params);

        // Featured dramas for the Netflix Hero Billboard Carousel
        $heroDramas = Database::fetchAll(
            "SELECT d.*, u.name as creator_name,
             (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as episode_count,
             (SELECT id FROM episodes WHERE drama_id = d.id ORDER BY episode_number ASC LIMIT 1) as first_episode_id
             FROM dramas d
             JOIN users u ON d.creator_id = u.id
             WHERE d.status = 'published'
             ORDER BY d.featured DESC, d.views_count DESC
             LIMIT 3"
        );

        // Ensure banner_url fallbacks
        foreach ($heroDramas as &$hd) {
            if (empty($hd['banner_url'])) {
                $hd['banner_url'] = ($hd['genre'] === 'Action') ? '/uploads/posters/dendam-billboard.jpg' : '/uploads/posters/ceo-billboard.jpg';
            }
        }

        // Row collections for Netflix slider rows
        $trendsNow = $dramas;
        $popular = Database::fetchAll(
            "SELECT d.*, u.name as creator_name,
             (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as episode_count,
             (SELECT id FROM episodes WHERE drama_id = d.id ORDER BY episode_number ASC LIMIT 1) as first_episode_id
             FROM dramas d
             JOIN users u ON d.creator_id = u.id
             WHERE d.status = 'published'
             ORDER BY d.views_count DESC LIMIT 8"
        );

        $premieres = Database::fetchAll(
            "SELECT d.*, u.name as creator_name,
             (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as episode_count,
             (SELECT id FROM episodes WHERE drama_id = d.id ORDER BY episode_number ASC LIMIT 1) as first_episode_id
             FROM dramas d
             JOIN users u ON d.creator_id = u.id
             WHERE d.status = 'published'
             ORDER BY d.created_at DESC LIMIT 8"
        );

        $genres = ['Semua', 'Romance', 'Action', 'Mystery', 'Crime', 'Comedy', 'Drama', 'Adventure'];

        Router::render('viewer/home', [
            'title' => 'Filemai - Tonton Siri & Drama Mini Menegak',
            'heroDramas' => $heroDramas,
            'dramas' => $dramas,
            'trendsNow' => $trendsNow,
            'popular' => $popular,
            'premieres' => $premieres,
            'genres' => $genres,
            'selectedGenre' => $genre ?: 'Semua',
            'activeTab' => $tab,
            'search' => $search,
        ]);
    }
}
