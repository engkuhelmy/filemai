<?php

namespace App\Http\Controllers;

use App\Models\Drama;
use App\Models\Episode;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EpisodeWatchController extends Controller
{
    /**
     * Display the 9:16 vertical video player and ad-gated interstitial theater.
     *
     * @param Drama $drama
     * @param Episode $episode
     * @param Request $request
     * @return View
     */
    public function show(Drama $drama, Episode $episode, Request $request): View
    {
        // Ensure episode belongs to this drama
        if ($episode->drama_id !== $drama->id) {
            abort(404, 'Episod tidak sah untuk drama ini.');
        }

        // Increment episode view count asynchronously
        $episode->increment('views_count');
        $drama->increment('views_count');

        // Retrieve all episodes for sidebar/drawer playlist, ordered by episode_number ASC
        $allEpisodes = $drama->episodes()->orderBy('episode_number', 'asc')->get();

        // Determine next and previous episodes
        $nextEpisode = $drama->episodes()
            ->where('episode_number', '>', $episode->episode_number)
            ->orderBy('episode_number', 'asc')
            ->first();

        $prevEpisode = $drama->episodes()
            ->where('episode_number', '<', $episode->episode_number)
            ->orderBy('episode_number', 'desc')
            ->first();

        // Check if authenticated user has active VIP Ad-Free subscription
        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;

        // Fetch 1 random active interstitial ad if viewer is free/guest
        $ad = null;
        if (!$isPremium) {
            $ad = Ad::getRandomActiveAd();
            if ($ad) {
                $ad->increment('views_count');
            }
        }

        return view('episodes.show', [
            'drama' => $drama,
            'episode' => $episode,
            'allEpisodes' => $allEpisodes,
            'nextEpisode' => $nextEpisode,
            'prevEpisode' => $prevEpisode,
            'isPremium' => $isPremium,
            'ad' => $ad,
            'title' => "Tonton {$episode->title} - {$drama->title} | Filemai",
        ]);
    }
}
