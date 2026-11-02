<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEpisodeRequest;
use App\Models\Drama;
use App\Models\Episode;
use App\Services\YouTubeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EpisodeController extends Controller
{
    /**
     * Show form for creating a new YouTube episode in this drama.
     */
    public function create(Drama $drama): View
    {
        // Suggest the next sequential episode number
        $nextEpNumber = ($drama->episodes()->max('episode_number') ?? 0) + 1;

        return view('creator.episodes.create', [
            'drama' => $drama,
            'nextEpNumber' => $nextEpNumber,
            'existingEpisodes' => $drama->episodes,
        ]);
    }

    /**
     * Store newly created YouTube episode for the drama.
     */
    public function store(StoreEpisodeRequest $request, Drama $drama): RedirectResponse
    {
        $validated = $request->validated();

        // Extract 11-char YouTube Video ID
        $videoId = YouTubeService::extractVideoId($validated['youtube_url']);

        // Create episode record
        $episode = $drama->episodes()->create([
            'episode_number' => $validated['episode_number'],
            'title' => $validated['title'],
            'video_source' => 'youtube',
            'youtube_video_id' => $videoId,
            'video_url' => $validated['youtube_url'],
            'duration' => $validated['duration'] ?? 60,
            'is_free_preview' => $request->boolean('is_free_preview', false),
        ]);

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', "Episod {$episode->episode_number} (\"{$episode->title}\") berjaya ditambah!");
    }

    /**
     * Delete an episode.
     */
    public function destroy(Drama $drama, Episode $episode): RedirectResponse
    {
        $episode->delete();

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', 'Episod berjaya dipadam.');
    }
}
