<?php

namespace App\Helpers;

class MediaHelper
{
    /**
     * Regex patterns for extracting 11-character YouTube Video IDs from:
     * - Standard watch URLs (youtube.com/watch?v=...)
     * - Mobile URLs (m.youtube.com/watch?v=...)
     * - Shortened URLs (youtu.be/...)
     * - YouTube Shorts (youtube.com/shorts/...)
     * - Embed links (youtube.com/embed/...)
     * - Live streams (youtube.com/live/...)
     * - Legacy v/ URLs (youtube.com/v/...)
     */
    public const YOUTUBE_PATTERNS = [
        // Standard, Shorts, Embed, Live, youtu.be
        '#(?:https?:\/\/)?(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|v\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})#i',
        // Fallback for query param v= anywhere
        '#[?&]v=([a-zA-Z0-9_-]{11})#i',
    ];

    /**
     * Common file extensions for direct storage videos.
     */
    public const DIRECT_VIDEO_EXTENSIONS = [
        'mp4', 'webm', 'mov', 'm4v', 'ogv', 'm3u8', 'ts'
    ];

    /**
     * Detect whether a given video URL/path is YouTube or direct storage.
     *
     * @param string|null $url
     * @return string 'youtube'|'storage'
     */
    public static function detectSource(?string $url): string
    {
        if (empty($url)) {
            return 'storage';
        }

        return self::isYouTube($url) ? 'youtube' : 'storage';
    }

    /**
     * Check if a given string or URL is a YouTube link or YouTube Video ID.
     *
     * @param string|null $url
     * @return bool
     */
    public static function isYouTube(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        return self::extractYouTubeId($url) !== null;
    }

    /**
     * Safely extract the 11-character YouTube Video ID.
     *
     * @param string|null $url
     * @return string|null 11-character video ID or null if not found
     */
    public static function extractYouTubeId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // Check if string is already a standalone 11-character YouTube ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        foreach (self::YOUTUBE_PATTERNS as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Check if a given URL is a direct video file (storage/CDN/local).
     *
     * @param string|null $url
     * @return bool
     */
    public static function isDirectVideo(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $url = trim($url);

        // Local upload path check
        if (str_starts_with($url, '/uploads/') || str_starts_with($url, 'uploads/')) {
            return true;
        }

        $cleanPath = parse_url($url, PHP_URL_PATH);
        if (!$cleanPath) {
            return false;
        }

        $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
        return in_array($extension, self::DIRECT_VIDEO_EXTENSIONS, true);
    }

    /**
     * Format a canonical YouTube URL (https://www.youtube.com/watch?v=...)
     *
     * @param string|null $url
     * @return string|null
     */
    public static function cleanYouTubeUrl(?string $url): ?string
    {
        $id = self::extractYouTubeId($url);
        return $id ? "https://www.youtube.com/watch?v={$id}" : null;
    }

    /**
     * Build an optimized YouTube Embed URL with JS API controls and parameters.
     *
     * @param string $videoId
     * @param array $extraParams
     * @return string
     */
    public static function getYouTubeEmbedUrl(string $videoId, array $extraParams = []): string
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

        $params = array_merge($defaultParams, $extraParams);
        return "https://www.youtube.com/embed/{$videoId}?" . http_build_query($params);
    }

    /**
     * Get high-quality thumbnail image for YouTube.
     *
     * @param string $videoId
     * @param string $quality 'maxresdefault' | 'hqdefault' | 'mqdefault' | 'default'
     * @return string
     */
    public static function getYouTubeThumbnail(string $videoId, string $quality = 'hqdefault'): string
    {
        return "https://img.youtube.com/vi/{$videoId}/{$quality}.jpg";
    }

    /**
     * Format duration in seconds into MM:SS or HH:MM:SS.
     *
     * @param int|null $seconds
     * @return string
     */
    public static function formatDuration(?int $seconds): string
    {
        $seconds = max(0, (int)$seconds);
        if ($seconds >= 3600) {
            return gmdate('H:i:s', $seconds);
        }
        return gmdate('i:s', $seconds);
    }
}
