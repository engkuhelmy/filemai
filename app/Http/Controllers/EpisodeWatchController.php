<?php

namespace App\Http\Controllers;

use App\Models\Drama;
use App\Models\Episode;
use App\Models\EpisodeView;
use App\Models\CreatorWallet;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class EpisodeWatchController extends Controller
{
    /**
     * Show the vertical video player for a specific episode of a drama.
     * Enforces VIP ad-free logic, random active interstitial ad for free viewers,
     * next episode auto-advancement, and episode playlist.
     */
    public function show(Drama $drama, $episode, Request $request): View
    {
        // Resolve episode by episode_number (e.g. 1, 2, 3) or by primary key ID
        if (!($episode instanceof Episode)) {
            $episodeModel = $drama->episodes()
                ->where('episode_number', $episode)
                ->first();

            if (!$episodeModel && is_numeric($episode)) {
                $episodeModel = $drama->episodes()
                    ->where('id', (int)$episode)
                    ->first();
            }

            if (!$episodeModel) {
                abort(404, "Episod #{$episode} tidak ditemui untuk siri drama ini.");
            }

            $episode = $episodeModel;
        }

        // Ensure episode belongs to this drama
        if ((int)$episode->drama_id !== (int)$drama->id) {
            abort(404, 'Episod tidak sah untuk drama ini.');
        }

        $user = $request->user();
        $isCreatorOrAdmin = $user && ($user->isAdmin() || (int)$drama->creator_id === (int)$user->id);

        // Feature 5: Visibility Check (Draft / Scheduled)
        if (!$isCreatorOrAdmin) {
            if ($episode->status === 'draft') {
                abort(404, 'Episod ini masih dalam draf dan belum diterbitkan.');
            }
            if ($episode->status === 'scheduled' && $episode->scheduled_at && $episode->scheduled_at->isFuture()) {
                abort(403, 'Episod ini dijadualkan untuk tayangan pada ' . $episode->scheduled_at->format('d/m/Y h:i A') . '.');
            }
        }

        // Record real view event with device, browser, IP and user analytics
        EpisodeView::recordView($drama, $episode, $request);

        // Credit creator watch earnings per 1-minute block (e.g. RM 0.0001/block)
        CreatorWallet::recordViewEarnings($drama, $episode);

        // Increment episode view count asynchronously (only if released or normal viewer)
        if ($episode->status === 'published' || ($episode->status === 'scheduled' && $episode->scheduled_at && $episode->scheduled_at->isPast()) || !$episode->status) {
            $episode->increment('views_count');
            $drama->increment('views_count');
        }

        // Retrieve episodes for sidebar/drawer playlist
        $episodesQuery = $drama->episodes();
        if (!$isCreatorOrAdmin) {
            $episodesQuery->where(function ($q) {
                $q->where('status', 'published')
                  ->orWhereNull('status')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'scheduled')
                         ->whereNotNull('scheduled_at')
                         ->where('scheduled_at', '<=', now());
                  });
            });
        }

        $allEpisodes = (clone $episodesQuery)->orderBy('episode_number', 'asc')->get();

        // Determine next and previous episodes
        $nextEpisode = (clone $episodesQuery)
            ->where('episode_number', '>', $episode->episode_number)
            ->orderBy('episode_number', 'asc')
            ->first();

        $prevEpisode = (clone $episodesQuery)
            ->where('episode_number', '<', $episode->episode_number)
            ->orderBy('episode_number', 'desc')
            ->first();

        // Check if authenticated user has active VIP Ad-Free subscription
        $isPremium = $user ? $user->isPremium() : false;

        // Fetch 1 random active interstitial ad if viewer is free/guest
        $ad = null;
        if (!$isPremium) {
            $ad = Ad::active()->inRandomOrder()->first();
        }

        return view('episodes.show', [
            'drama' => $drama,
            'episode' => $episode,
            'allEpisodes' => $allEpisodes,
            'nextEpisode' => $nextEpisode,
            'prevEpisode' => $prevEpisode,
            'isPremium' => $isPremium,
            'ad' => $ad,
            'isCreatorPreview' => $isCreatorOrAdmin && ($episode->status !== 'published' && !empty($episode->status)),
            'title' => "Tonton {$episode->title} - {$drama->title} | Filemai",
        ]);
    }
}
