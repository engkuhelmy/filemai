<?php

namespace App\Controllers;

use App\Database;
use App\Auth;
use App\Router;

class WatchController
{
    public function watch(string $episodeId): void
    {
        $episode = Database::fetchOne(
            "SELECT e.*, d.title as drama_title, d.slug as drama_slug, d.poster_url as drama_poster,
             d.genre as drama_genre, d.creator_id, u.name as creator_name
             FROM episodes e
             JOIN dramas d ON e.drama_id = d.id
             JOIN users u ON d.creator_id = u.id
             WHERE e.id = ?",
            [$episodeId]
        );

        if (!$episode) {
            http_response_code(404);
            Router::render('errors/404', ['title' => 'Episod Tidak Ditemui']);
            return;
        }

        // Increment view counts
        Database::execute("UPDATE episodes SET views_count = views_count + 1 WHERE id = ?", [$episode['id']]);
        Database::execute("UPDATE dramas SET views_count = views_count + 1 WHERE id = ?", [$episode['drama_id']]);

        // Fetch all episodes for the drawer / switcher
        $allEpisodes = Database::fetchAll(
            "SELECT * FROM episodes WHERE drama_id = ? ORDER BY episode_number ASC",
            [$episode['drama_id']]
        );

        // Find Next and Prev episodes
        $nextEpisode = null;
        $prevEpisode = null;
        foreach ($allEpisodes as $index => $ep) {
            if ($ep['id'] == $episode['id']) {
                $prevEpisode = $allEpisodes[$index - 1] ?? null;
                $nextEpisode = $allEpisodes[$index + 1] ?? null;
                break;
            }
        }

        // Attach HLS Adaptive Bitrate Stream URL if available
        $hlsFile = dirname(__DIR__, 2) . '/uploads/videos/hls/ep.m3u8';
        $hasHls = file_exists($hlsFile);
        $episode['hls_url'] = $hasHls ? '/uploads/videos/hls/ep.m3u8' : null;
        if ($nextEpisode) {
            $nextEpisode['hls_url'] = $hasHls ? '/uploads/videos/hls/ep.m3u8' : null;
        }
        if ($prevEpisode) {
            $prevEpisode['hls_url'] = $hasHls ? '/uploads/videos/hls/ep.m3u8' : null;
        }

        // Interstitial Ad Logic
        $isPremium = Auth::isPremium();
        $ad = null;
        if (!$isPremium) {
            // Fetch an active random ad
            $ad = Database::fetchOne("SELECT * FROM ads WHERE is_active = 1 ORDER BY RAND() LIMIT 1");
            if ($ad) {
                // Increment ad impressions
                Database::execute("UPDATE ads SET views_count = views_count + 1 WHERE id = ?", [$ad['id']]);
            }
        }

        Router::render('viewer/watch', [
            'title' => $episode['title'] . ' | ' . $episode['drama_title'],
            'episode' => $episode,
            'allEpisodes' => $allEpisodes,
            'nextEpisode' => $nextEpisode,
            'prevEpisode' => $prevEpisode,
            'isPremium' => $isPremium,
            'ad' => $ad,
            'noLayout' => true, // Vertical theater player uses its own tailored fullscreen layout
        ]);
    }
}
