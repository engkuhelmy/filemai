<?php

namespace App\Controllers;

use App\Database;
use App\Router;

class ApiController
{
    public function recordView(string $episodeId): void
    {
        $id = (int)$episodeId;
        Database::execute("UPDATE episodes SET views_count = views_count + 1 WHERE id = ?", [$id]);
        $ep = Database::fetchOne("SELECT drama_id FROM episodes WHERE id = ?", [$id]);
        if ($ep) {
            Database::execute("UPDATE dramas SET views_count = views_count + 1 WHERE id = ?", [$ep['drama_id']]);
        }
        Router::json(['success' => true]);
    }

    public function recordAdClick(string $adId): void
    {
        $id = (int)$adId;
        Database::execute("UPDATE ads SET clicks_count = clicks_count + 1 WHERE id = ?", [$id]);
        Router::json(['success' => true]);
    }

    public function randomAd(): void
    {
        $ad = Database::fetchOne("SELECT * FROM ads WHERE is_active = 1 ORDER BY RAND() LIMIT 1");
        if ($ad) {
            Database::execute("UPDATE ads SET views_count = views_count + 1 WHERE id = ?", [$ad['id']]);
            Router::json(['success' => true, 'ad' => $ad]);
        } else {
            Router::json(['success' => false, 'message' => 'No active ads']);
        }
    }
}
