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

        // Attach HLS stream ONLY if the episode specifically uses an .m3u8 URL or has an episode-specific HLS file
        $isDirect = ($episode['video_source'] ?? 'direct') === 'direct';
        $episode['hls_url'] = null;
        if ($isDirect && !empty($episode['video_url']) && str_ends_with(strtolower($episode['video_url']), '.m3u8')) {
            $episode['hls_url'] = $episode['video_url'];
        } else if ($isDirect) {
            $epHlsFile = dirname(__DIR__, 2) . '/uploads/videos/hls/ep' . $episode['id'] . '.m3u8';
            if (file_exists($epHlsFile)) {
                $episode['hls_url'] = '/uploads/videos/hls/ep' . $episode['id'] . '.m3u8';
            }
        }

        if ($nextEpisode) {
            $nextIsDirect = ($nextEpisode['video_source'] ?? 'direct') === 'direct';
            $nextEpisode['hls_url'] = ($nextIsDirect && !empty($nextEpisode['video_url']) && str_ends_with(strtolower($nextEpisode['video_url']), '.m3u8')) ? $nextEpisode['video_url'] : null;
        }
        if ($prevEpisode) {
            $prevIsDirect = ($prevEpisode['video_source'] ?? 'direct') === 'direct';
            $prevEpisode['hls_url'] = ($prevIsDirect && !empty($prevEpisode['video_url']) && str_ends_with(strtolower($prevEpisode['video_url']), '.m3u8')) ? $prevEpisode['video_url'] : null;
        }

        // Attach video poster / thumbnail
        if (($episode['video_source'] ?? '') === 'youtube' && !empty($episode['youtube_video_id'])) {
            $episode['poster_url'] = "https://img.youtube.com/vi/{$episode['youtube_video_id']}/hqdefault.jpg";
        } else {
            $epThumb = dirname(__DIR__, 2) . '/uploads/posters/ep' . $episode['id'] . '_thumb.jpg';
            $episode['poster_url'] = file_exists($epThumb) ? '/uploads/posters/ep' . $episode['id'] . '_thumb.jpg' : ($episode['drama_poster'] ?? '');
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

    public function watchByDramaAndEpisode(string $dramaSlug, string $episodeNumber): void
    {
        $ep = Database::fetchOne(
            "SELECT e.id FROM episodes e JOIN dramas d ON e.drama_id = d.id WHERE d.slug = ? AND e.episode_number = ?",
            [$dramaSlug, (int)$episodeNumber]
        );

        if (!$ep) {
            http_response_code(404);
            Router::render('errors/404', ['title' => 'Episod Tidak Ditemui']);
            return;
        }

        $this->watch((string)$ep['id']);
    }
}
