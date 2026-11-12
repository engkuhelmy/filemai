<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Drama;
use App\Models\Episode;
use App\Models\PlaybackProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TvApiController extends Controller
{
    /**
     * Bootstrap payload for Android TV and Smart TV apps.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        $sessionId = $request->input('session_id') ?? $request->header('X-Session-ID') ?? session()->getId();

        $featuredDramas = Drama::where('status', 'published')
            ->where('featured', true)
            ->withCount('episodes')
            ->take(5)
            ->get();

        if ($featuredDramas->isEmpty()) {
            $featuredDramas = Drama::where('status', 'published')
                ->withCount('episodes')
                ->take(5)
                ->get();
        }

        $genres = ['Romance', 'Action', 'Thriller', 'CEO', 'Revenge', 'Drama', 'Komedi', 'Fantasi'];

        // Continue watching history
        $continueWatching = [];
        $progressQuery = PlaybackProgress::with(['drama', 'episode'])
            ->where('completed', false)
            ->where('progress_seconds', '>', 5);

        if ($user) {
            $progressQuery->where('user_id', $user->id);
        } elseif ($sessionId) {
            $progressQuery->where('session_id', $sessionId);
        }

        $continueWatching = $progressQuery->orderBy('last_watched_at', 'desc')
            ->take(10)
            ->get()
            ->filter(fn($p) => $p->drama && $p->episode)
            ->map(function ($p) {
                return [
                    'progress_id' => $p->id,
                    'drama_id' => $p->drama->id,
                    'drama_title' => $p->drama->title,
                    'drama_slug' => $p->drama->slug,
                    'poster_url' => $p->drama->poster_url,
                    'banner_url' => $p->drama->banner_url,
                    'episode_id' => $p->episode->id,
                    'episode_number' => $p->episode->episode_number,
                    'episode_title' => $p->episode->title,
                    'progress_seconds' => $p->progress_seconds,
                    'duration_seconds' => $p->duration_seconds,
                    'percentage' => $p->duration_seconds > 0 ? round(($p->progress_seconds / $p->duration_seconds) * 100) : 0,
                    'last_watched_at' => $p->last_watched_at?->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'app' => [
                'name' => 'Filemai TV',
                'version' => '2.1.0',
                'cast_app_id' => 'CC1AD845', // Default Media Receiver
                'airplay_enabled' => true,
                'remote_playback_enabled' => true,
            ],
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_vip' => $user->isPremium(),
            ] : null,
            'featured' => $featuredDramas->map(fn($d) => $this->formatDramaItem($d)),
            'continue_watching' => $continueWatching,
            'genres' => $genres,
        ]);
    }

    /**
     * Categorized list of dramas for TV shelves (Trending, Popular, Categories).
     */
    public function dramas(Request $request): JsonResponse
    {
        $genre = $request->query('genre');
        $query = Drama::where('status', 'published')->withCount('episodes');

        if ($genre && $genre !== 'all') {
            $query->where('genre', $genre);
        }

        $trending = (clone $query)->orderBy('views_count', 'desc')->take(10)->get();
        $popular = (clone $query)->where('featured', true)->orderBy('views_count', 'desc')->take(10)->get();
        if ($popular->isEmpty()) {
            $popular = (clone $query)->inRandomOrder()->take(10)->get();
        }
        $recent = (clone $query)->orderBy('created_at', 'desc')->take(10)->get();

        // Categorized rows
        $categories = [];
        $sampleGenres = ['Romance', 'Action', 'Thriller', 'CEO', 'Revenge'];
        foreach ($sampleGenres as $g) {
            $items = Drama::where('status', 'published')
                ->where('genre', $g)
                ->withCount('episodes')
                ->orderBy('views_count', 'desc')
                ->take(8)
                ->get();

            if ($items->isNotEmpty()) {
                $categories[] = [
                    'genre' => $g,
                    'title' => "Koleksi {$g}",
                    'items' => $items->map(fn($d) => $this->formatDramaItem($d)),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'shelves' => [
                [
                    'id' => 'trending',
                    'title' => '🔥 Sedang Hangat & Trending di Malaysia',
                    'items' => $trending->map(fn($d) => $this->formatDramaItem($d)),
                ],
                [
                    'id' => 'popular',
                    'title' => '⭐ Paling Popular Pilihan Penonton',
                    'items' => $popular->map(fn($d) => $this->formatDramaItem($d)),
                ],
                [
                    'id' => 'recent',
                    'title' => '🆕 Siri Baharu Diterbitkan',
                    'items' => $recent->map(fn($d) => $this->formatDramaItem($d)),
                ],
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Drama details and full episode playlist for TV screen.
     */
    public function showDrama(Drama $drama): JsonResponse
    {
        $episodes = $drama->episodes()
            ->where(function ($q) {
                $q->where('status', 'published')->orWhereNull('status');
            })
            ->orderBy('episode_number', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'drama' => array_merge($this->formatDramaItem($drama), [
                'synopsis' => $drama->synopsis,
                'cast' => $drama->cast_list,
                'content_warnings' => $drama->content_warnings_list,
                'social_hashtag' => $drama->social_hashtag,
                'release_status' => $drama->release_status,
                'episodes' => $episodes->map(fn($ep) => $this->formatEpisodeItem($ep, $drama)),
            ]),
        ]);
    }

    /**
     * Get episode stream and playback details.
     */
    public function showEpisode(Request $request, $episode): JsonResponse
    {
        // Resolve episode by ID or model
        if (!($episode instanceof Episode)) {
            $episodeModel = is_numeric($episode) ? Episode::find((int)$episode) : null;
            if (!$episodeModel) {
                return response()->json(['status' => 'error', 'message' => 'Episod tidak ditemui'], 404);
            }
            $episode = $episodeModel;
        }

        $drama = $episode->drama;
        if (!$drama) {
            return response()->json(['status' => 'error', 'message' => 'Drama tidak ditemui'], 404);
        }

        $allEpisodes = $drama->episodes()->orderBy('episode_number', 'asc')->get();

        $next = $drama->episodes()
            ->where('episode_number', '>', $episode->episode_number)
            ->orderBy('episode_number', 'asc')
            ->first();

        $prev = $drama->episodes()
            ->where('episode_number', '<', $episode->episode_number)
            ->orderBy('episode_number', 'desc')
            ->first();

        // Check resume timestamp
        $user = $request->user();
        $sessionId = $request->input('session_id') ?? session()->getId();
        $progress = PlaybackProgress::getProgress($episode->id, $user?->id, $sessionId);

        return response()->json([
            'status' => 'success',
            'episode' => $this->formatEpisodeItem($episode, $drama),
            'resume' => [
                'has_progress' => $progress && $progress->progress_seconds > 5 && !$progress->completed,
                'progress_seconds' => $progress ? $progress->progress_seconds : 0,
                'duration_seconds' => $progress ? $progress->duration_seconds : ($episode->duration ?: 60),
            ],
            'playlist' => $allEpisodes->map(fn($ep) => [
                'id' => $ep->id,
                'episode_number' => $ep->episode_number,
                'title' => $ep->title,
                'duration' => $ep->duration,
                'thumbnail_url' => $ep->thumbnail_url,
                'is_free' => $ep->is_free_preview,
                'is_coming_soon' => $ep->isComingSoon(),
            ]),
            'next_episode' => $next ? [
                'id' => $next->id,
                'episode_number' => $next->episode_number,
                'title' => $next->title,
            ] : null,
            'prev_episode' => $prev ? [
                'id' => $prev->id,
                'episode_number' => $prev->episode_number,
                'title' => $prev->title,
            ] : null,
        ]);
    }

    /**
     * Save playback progress for resume functionality on TV / Web.
     */
    public function saveProgress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'drama_id' => 'required|integer',
            'episode_id' => 'required|integer',
            'progress_seconds' => 'required|integer|min:0',
            'duration_seconds' => 'nullable|integer|min:1',
            'session_id' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $userId = $user?->id;
        $sessionId = $validated['session_id'] ?? session()->getId();
        $durationSeconds = $validated['duration_seconds'] ?? 60;

        $record = PlaybackProgress::recordProgress(
            (int)$validated['drama_id'],
            (int)$validated['episode_id'],
            (int)$validated['progress_seconds'],
            (int)$durationSeconds,
            $userId,
            $sessionId
        );

        return response()->json([
            'status' => 'success',
            'saved' => true,
            'progress' => [
                'progress_seconds' => $record->progress_seconds,
                'duration_seconds' => $record->duration_seconds,
                'completed' => $record->completed,
            ],
        ]);
    }

    /**
     * Format a drama model into a standard JSON payload.
     */
    private function formatDramaItem(Drama $drama): array
    {
        $typeBadge = $drama->type_badge ?? [
            'type' => 'drama',
            'label' => 'Drama',
        ];

        return [
            'id' => $drama->id,
            'title' => $drama->title,
            'slug' => $drama->slug,
            'genre' => $drama->genre,
            'genres' => $drama->genres_list,
            'format_type' => $drama->effective_type,
            'format_label' => $typeBadge['label'] ?? 'Drama',
            'poster_url' => $drama->poster_url,
            'banner_url' => $drama->banner_url ?: $drama->poster_url,
            'trailer_url' => $drama->trailer_url,
            'age_rating' => $drama->age_rating ?? '13+',
            'views_count' => (int)$drama->views_count,
            'episodes_count' => $drama->episodes_count ?? $drama->episodes()->count(),
            'accent_color' => $drama->effective_accent_color,
            'web_url' => url('/drama/' . $drama->slug),
            'tv_url' => url('/tv/watch/' . $drama->slug),
        ];
    }

    /**
     * Format an episode model into a standard JSON payload for TV playback.
     */
    private function formatEpisodeItem(Episode $episode, Drama $drama): array
    {
        $ytId = $episode->effective_youtube_id;
        $isYt = $episode->isYouTube() || !empty($ytId);

        return [
            'id' => $episode->id,
            'drama_id' => $drama->id,
            'drama_slug' => $drama->slug,
            'drama_title' => $drama->title,
            'episode_number' => $episode->episode_number,
            'title' => $episode->title,
            'description' => $episode->description,
            'video_type' => $isYt ? 'youtube' : 'storage',
            'is_youtube' => $isYt,
            'youtube_video_id' => $ytId,
            'video_url' => $episode->video_url,
            'cast_stream_url' => $episode->video_url ?: ($ytId ? "https://www.youtube.com/watch?v={$ytId}" : null),
            'thumbnail_url' => $episode->thumbnail_url,
            'duration_seconds' => $episode->duration ?: 60,
            'formatted_duration' => $episode->formatted_duration,
            'is_free' => (bool)$episode->is_free_preview,
            'is_coming_soon' => $episode->isComingSoon(),
        ];
    }
}
