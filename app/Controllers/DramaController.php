<?php

namespace App\Controllers;

use App\Database;
use App\Router;

class DramaController
{
    public function show(string $slug): void
    {
        $drama = Database::fetchOne(
            "SELECT d.*, u.name as creator_name, u.avatar_url as creator_avatar
             FROM dramas d
             JOIN users u ON d.creator_id = u.id
             WHERE d.slug = ?",
            [$slug]
        );

        if (!$drama) {
            http_response_code(404);
            Router::render('errors/404', ['title' => 'Drama Tidak Ditemui']);
            return;
        }

        $episodes = Database::fetchAll(
            "SELECT * FROM episodes WHERE drama_id = ? ORDER BY episode_number ASC",
            [$drama['id']]
        );

        // Related dramas
        $related = Database::fetchAll(
            "SELECT * FROM dramas WHERE id != ? AND status = 'published' AND genre = ? LIMIT 4",
            [$drama['id'], $drama['genre']]
        );

        Router::render('viewer/drama_detail', [
            'title' => $drama['title'] . ' - Tonton di Filemai',
            'drama' => $drama,
            'episodes' => $episodes,
            'related' => $related,
        ]);
    }
}
