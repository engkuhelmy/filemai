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

        // Fallback data if database has no published dramas or null fields
        if (empty($heroDramas)) {
            $heroDramas = [
                [
                    'id' => 2,
                    'title' => 'Dendam Pewaris Yang Dibuang',
                    'slug' => 'dendam-pewaris-yang-dibuang',
                    'synopsis' => 'Dibuang oleh keluarga konglomeratnya 10 tahun lalu dan dihina sebagai pengemis jalanan. Kini, sang naga tidur bangkit kembali sebagai penguasa kewangan global terkuat untuk menuntut setiap titis air mata dan maruah yang telah dirampas!',
                    'poster_url' => '/uploads/posters/dendam-pewaris.jpg',
                    'banner_url' => '/uploads/posters/dendam-billboard.jpg',
                    'trailer_url' => '/uploads/videos/sample_reel.mp4',
                    'genre' => 'Action',
                    'episode_count' => 3,
                    'first_episode_id' => 5,
                    'views_count' => 28922,
                    'rating' => '8.9',
                    'hook' => '🔥 #1 Paling Trending di Malaysia',
                ],
                [
                    'id' => 1,
                    'title' => 'Jodoh Kontrak CEO Dingin',
                    'slug' => 'jodoh-kontrak-ceo-dingin',
                    'synopsis' => 'Seorang gadis biasa terpaksa menandatangani kontrak perkahwinan palsu bernilai RM1 Juta dengan CEO paling dingin di kota demi menyelamatkan perniagaan keluarganya. Namun di sebalik kedinginannya, tersimpan rahsia besar masa silam.',
                    'poster_url' => '/uploads/posters/jodoh-kontrak-ceo.jpg',
                    'banner_url' => '/uploads/posters/ceo-billboard.jpg',
                    'trailer_url' => '/uploads/videos/sample_reel.mp4',
                    'genre' => 'Romance',
                    'episode_count' => 4,
                    'first_episode_id' => 1,
                    'views_count' => 15459,
                    'rating' => '8.5',
                    'hook' => '💖 Top 10 Pilihan Peminat Romance',
                ]
            ];
        } else {
            foreach ($heroDramas as &$hd) {
                // Ensure high-res landscape billboards are used
                if (empty($hd['banner_url']) || str_ends_with($hd['banner_url'], '-pewaris.jpg') || str_ends_with($hd['banner_url'], '-ceo.jpg') || str_ends_with($hd['banner_url'], '-isteri.jpg')) {
                    if (str_contains($hd['slug'], 'dendam') || $hd['genre'] === 'Action') {
                        $hd['banner_url'] = '/uploads/posters/dendam-billboard.jpg';
                    } else {
                        $hd['banner_url'] = '/uploads/posters/ceo-billboard.jpg';
                    }
                }
                $hd['trailer_url'] = '/uploads/videos/sample_reel.mp4';
                $hd['rating'] = ($hd['genre'] === 'Action') ? '8.9' : '8.5';
                $hd['hook'] = ($hd['genre'] === 'Action') ? '🔥 #1 Paling Trending di Malaysia' : '💖 Top Pilihan Penonton';
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
