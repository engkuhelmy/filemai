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
     * Store newly created episode for the drama (direct storage MP4 or YouTube).
     */
    public function store(StoreEpisodeRequest $request, Drama $drama): RedirectResponse
    {
        $validated = $request->validated();
        $videoType = $validated['video_type'] ?? 'storage';

        $youtubeVideoId = null;
        $videoUrl = null;

        if ($videoType === 'youtube') {
            $rawUrl = $validated['youtube_url'] ?? '';
            $youtubeVideoId = \App\Helpers\MediaHelper::extractYouTubeId($rawUrl);
            $videoUrl = \App\Helpers\MediaHelper::cleanYouTubeUrl($rawUrl) ?? $rawUrl;
            $videoSource = 'youtube';
        } else {
            // Storage file upload or CDN URL
            if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
                $file = $request->file('video_file');
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = 'ep_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                
                // Destination in public uploads directory
                $targetDir = public_path('uploads/videos');
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                
                $file->move($targetDir, $filename);
                $videoUrl = '/uploads/videos/' . $filename;
            } elseif (!empty($validated['video_url'])) {
                $videoUrl = trim($validated['video_url']);
            } else {
                $videoUrl = '/uploads/videos/sample_reel.mp4';
            }

            $videoSource = 'direct';
            $youtubeVideoId = null;
        }

        // Create episode record
        $episode = $drama->episodes()->create([
            'episode_number' => $validated['episode_number'],
            'title' => $validated['title'],
            'video_type' => $videoType,
            'video_source' => $videoSource,
            'youtube_video_id' => $youtubeVideoId,
            'video_url' => $videoUrl,
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
