<?php

namespace App\Http\Controllers;

use App\Models\Drama;
use App\Models\Episode;
use App\Models\PlaybackProgress;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TvController extends Controller
{
    /**
     * Dedicated 10-Foot Leanback UI for Smart TVs and Android TV browsers.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $sessionId = session()->getId();

        // Featured billboard dramas
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

        // Continue watching history
        $progressQuery = PlaybackProgress::with(['drama', 'episode'])
            ->where('completed', false)
            ->where('progress_seconds', '>', 5);

        if ($user) {
            $progressQuery->where('user_id', $user->id);
        } else {
            $progressQuery->where('session_id', $sessionId);
        }

        $continueWatching = $progressQuery->orderBy('last_watched_at', 'desc')
            ->take(12)
            ->get()
            ->filter(fn($p) => $p->drama && $p->episode);

        // Trending
        $trendingDramas = Drama::where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->withCount('episodes')
            ->take(12)
            ->get();

        // Latest releases
        $latestDramas = Drama::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->withCount('episodes')
            ->take(12)
            ->get();

        // Genre rows
        $genres = ['Romance', 'Action', 'Thriller', 'CEO', 'Revenge', 'Drama'];
        $genreDramas = [];
        foreach ($genres as $genre) {
            $items = Drama::where('status', 'published')
                ->where(function ($q) use ($genre) {
                    $q->where('genre', 'like', "%{$genre}%")
                      ->orWhere('genres', 'like', "%{$genre}%");
                })
                ->withCount('episodes')
                ->take(10)
                ->get();

            if ($items->isNotEmpty()) {
                $genreDramas[$genre] = $items;
            }
        }

        return view('tv.index', compact(
            'featuredDramas',
            'continueWatching',
            'trendingDramas',
            'latestDramas',
            'genreDramas'
        ));
    }

    /**
     * TV-optimized Leanback Player for big screens with D-Pad remote navigation.
     */
    public function watch(Request $request, string $slug, $episodeNumber = 1): View
    {
        $drama = Drama::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $episode = $drama->episodes()
            ->where('episode_number', $episodeNumber)
            ->first();

        if (!$episode) {
            $episode = $drama->episodes()
                ->where('id', $episodeNumber)
                ->first();
        }

        if (!$episode) {
            $episode = $drama->episodes()
                ->orderBy('episode_number', 'asc')
                ->firstOrFail();
        }

        $episodes = $drama->episodes()
            ->orderBy('episode_number', 'asc')
            ->get();

        $nextEpisode = $drama->episodes()
            ->where('episode_number', '>', $episode->episode_number)
            ->orderBy('episode_number', 'asc')
            ->first();

        $prevEpisode = $drama->episodes()
            ->where('episode_number', '<', $episode->episode_number)
            ->orderBy('episode_number', 'desc')
            ->first();

        // Resume progress
        $user = $request->user();
        $sessionId = session()->getId();
        $progress = PlaybackProgress::getProgress($episode->id, $user?->id, $sessionId);

        // Increment views
        $episode->increment('views_count');
        $drama->increment('views_count');

        return view('tv.watch', compact(
            'drama',
            'episode',
            'episodes',
            'nextEpisode',
            'prevEpisode',
            'progress'
        ));
    }
}
