<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use DateInterval;
use Throwable;

class YouTubeService
{
    /**
     * Reliably extracts the 11-character YouTube Video ID from standard watch URLs,
     * shortened youtu.be links, YouTube Shorts, embed links, or raw video IDs.
     *
     * @param string $url
     * @return string|null
     */
    public static function extractVideoId(string $url): ?string
    {
        $url = trim($url);
        if (empty($url)) {
            return null;
        }

        // Direct 11-character YouTube ID check
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        // Standard, shortened, shorts, embed, and live patterns
        $patterns = [
            // Matches: youtube.com/watch?v=..., youtube.com/shorts/..., youtube.com/embed/..., youtube.com/live/..., youtu.be/...
            '#(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|v\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})#i',
            // Fallback for m.youtube.com or query params at end
            '#[?&]v=([a-zA-Z0-9_-]{11})#i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Check if a given string is a valid 11-character YouTube Video ID.
     */
    public static function isValidId(?string $id): bool
    {
        return is_string($id) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) === 1;
    }

    /**
     * Check if a given URL or string resolves to a valid YouTube Video ID.
     */
    public static function isValidUrl(string $url): bool
    {
        $id = self::extractVideoId($url);
        return self::isValidId($id);
    }

    /**
     * Get YouTube high-quality thumbnail URL.
     * Qualities: 'maxresdefault', 'hqdefault', 'mqdefault', 'default'
     */
    public static function getThumbnailUrl(string $videoId, string $quality = 'hqdefault'): string
    {
        return "https://img.youtube.com/vi/{$videoId}/{$quality}.jpg";
    }

    /**
     * Generate standard embed URL with vertical and mobile-optimized parameters.
     */
    public static function getEmbedUrl(string $videoId, array $params = []): string
    {
        $defaultParams = [
            'autoplay' => 1,
            'mute' => 1,
            'playsinline' => 1,
            'controls' => 1,
            'rel' => 0,
            'modestbranding' => 1,
            'enablejsapi' => 1,
            'fs' => 1,
        ];

        $merged = array_merge($defaultParams, $params);
        return "https://www.youtube.com/embed/{$videoId}?" . http_build_query($merged);
    }

    /**
     * Data Extraction & Auto-Fill from YouTube:
     * - Highest quality thumbnail (maxresdefault or hqdefault)
     * - Video Title
     * - Video Description / Synopsis
     * - Video Duration in total seconds (converted from ISO 8601 or approxDurationMs)
     */
    public static function extractVideoData(string $url): array
    {
        $videoId = self::extractVideoId($url);
        if (!$videoId) {
            return [
                'success' => false,
                'message' => 'Pautan URL YouTube tidak sah. Sila semak pautan anda.',
            ];
        }

        $title = '';
        $description = '';
        $durationSeconds = 60;
        $durationIso = 'PT1M0S';
        $fetched = false;

        // 1. Try YouTube Data API v3 if API key is configured
        $apiKey = config('services.youtube.api_key') ?: env('YOUTUBE_API_KEY');
        if (!empty($apiKey)) {
            try {
                $apiRes = Http::timeout(5)->get('https://www.googleapis.com/youtube/v3/videos', [
                    'part' => 'snippet,contentDetails',
                    'id' => $videoId,
                    'key' => $apiKey,
                ]);

                if ($apiRes->successful() && !empty($apiRes->json('items'))) {
                    $item = $apiRes->json('items')[0];
                    $title = $item['snippet']['title'] ?? '';
                    $description = $item['snippet']['description'] ?? '';
                    if (!empty($item['contentDetails']['duration'])) {
                        $durationIso = $item['contentDetails']['duration'];
                        $durationSeconds = self::parseIso8601Duration($durationIso);
                    }
                    $fetched = true;
                }
            } catch (Throwable $e) {
                // Graceful fallback to oEmbed + scraper
            }
        }

        // 2. Fallback to YouTube oEmbed + Page Scraper (No API key required)
        if (!$fetched) {
            try {
                $oembedUrl = 'https://www.youtube.com/oembed?url=' . urlencode("https://www.youtube.com/watch?v={$videoId}") . '&format=json';
                $oembedRes = Http::timeout(5)->get($oembedUrl);

                if ($oembedRes->successful()) {
                    $oembed = $oembedRes->json();
                    $title = $oembed['title'] ?? '';
                    $fetched = true;
                }
            } catch (Throwable $e) {
                // Continues
            }

            try {
                $pageRes = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'ms,en-US;q=0.9,en;q=0.8',
                ])->timeout(6)->get("https://www.youtube.com/watch?v={$videoId}");

                if ($pageRes->successful()) {
                    $html = $pageRes->body();

                    if (empty($title) && preg_match('/<meta property="og:title" content="([^"]+)"/i', $html, $m)) {
                        $title = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $fetched = true;
                    }

                    if (preg_match('/<meta property="og:description" content="([^"]*)"/i', $html, $m)) {
                        $description = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }

                    if (preg_match('/"approxDurationMs":"(\d+)"/', $html, $m)) {
                        $durationSeconds = (int)round(((int)$m[1]) / 1000);
                    } elseif (preg_match('/<meta itemprop="duration" content="([^"]+)"/i', $html, $m)) {
                        $durationIso = $m[1];
                        $durationSeconds = self::parseIso8601Duration($durationIso);
                    }
                }
            } catch (Throwable $e) {
                // Continues
            }
        }

        if (!$fetched || empty($title)) {
            return [
                'success' => false,
                'message' => 'Gagal memuat turun data video. Pastikan pautan sah dan video adalah Public/Unlisted.',
            ];
        }

        // 3. Highest Quality Thumbnail Resolution Check
        $maxresUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
        $thumbUrl = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
        $thumbQuality = 'hqdefault';

        try {
            $maxresRes = Http::timeout(4)->get($maxresUrl);
            if ($maxresRes->successful() && strlen($maxresRes->body()) > 4000) {
                $thumbUrl = $maxresUrl;
                $thumbQuality = 'maxresdefault';
            }
        } catch (Throwable $e) {
            // Keep hqdefault fallback
        }

        return [
            'success' => true,
            'video_id' => $videoId,
            'title' => $title,
            'description' => $description,
            'duration_seconds' => max(5, $durationSeconds),
            'duration_iso' => $durationIso,
            'thumbnail_url' => $thumbUrl,
            'thumbnail_quality' => $thumbQuality,
        ];
    }

    /**
     * Convert ISO 8601 duration (e.g. PT1M45S, PT3M, PT50S, PT1H2M10S) to total seconds.
     */
    public static function parseIso8601Duration(string $duration): int
    {
        try {
            $interval = new DateInterval($duration);
            return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
        } catch (Throwable $e) {
            if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/i', $duration, $m)) {
                $h = isset($m[1]) ? (int)$m[1] : 0;
                $i = isset($m[2]) ? (int)$m[2] : 0;
                $s = isset($m[3]) ? (int)$m[3] : 0;
                return ($h * 3600) + ($i * 60) + $s;
            }
            return 60;
        }
    }
}
