<?php

namespace App\Services;

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
}
