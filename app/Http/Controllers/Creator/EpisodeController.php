<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEpisodeRequest;
use App\Models\Drama;
use App\Models\Episode;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EpisodeController extends Controller
{
    /**
     * Show form for creating a new episode in this drama.
     */
    public function create(Drama $drama): View
    {
        // Suggest the next sequential episode number
        $nextEpNumber = ($drama->episodes()->max('episode_number') ?? 0) + 1;

        return view('creator.episodes.create', [
            'drama' => $drama,
            'nextEpNumber' => $nextEpNumber,
            'existingEpisodes' => $drama->episodes()->orderBy('episode_number', 'asc')->get(),
        ]);
    }

    /**
     * Store newly created episode for the drama (direct storage MP4, YouTube, or Coming Soon).
     */
    public function store(StoreEpisodeRequest $request, Drama $drama): RedirectResponse
    {
        $validated = $request->validated();
        $videoType = $validated['video_type'] ?? 'storage';

        $youtubeVideoId = null;
        $videoUrl = null;
        $videoSource = 'youtube';
        $isComingSoon = false;

        if ($videoType === 'coming_soon') {
            $isComingSoon = true;
        } elseif ($videoType === 'youtube') {
            $rawUrl = $validated['youtube_url'] ?? '';
            $youtubeVideoId = \App\Helpers\MediaHelper::extractYouTubeId($rawUrl);
            if (!empty($youtubeVideoId)) {
                $videoUrl = \App\Helpers\MediaHelper::cleanYouTubeUrl($rawUrl) ?? $rawUrl;
                $videoSource = 'youtube';
            } else {
                // If YouTube URL is blank or invalid, auto mark as coming soon
                $isComingSoon = true;
            }
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
                $videoSource = 'direct';
            } elseif (!empty($validated['video_url'])) {
                $videoUrl = trim($validated['video_url']);
                $videoSource = 'direct';
            } else {
                // No video provided -> automatically coming soon!
                $isComingSoon = true;
                $videoSource = 'direct';
                $videoUrl = null;
            }
        }

        // Create episode record
        $episode = $drama->episodes()->create([
            'episode_number' => $validated['episode_number'],
            'title' => $validated['title'],
            'video_type' => $isComingSoon ? 'storage' : $videoType,
            'video_source' => $videoSource,
            'youtube_video_id' => $youtubeVideoId,
            'video_url' => $videoUrl,
            'duration' => $validated['duration'] ?? 60,
            'is_free_preview' => $request->boolean('is_free_preview', false),
            'is_coming_soon' => $isComingSoon,
        ]);

        $statusMsg = $isComingSoon 
            ? "Episod {$episode->episode_number} (\"{$episode->title}\") berjaya ditambah dengan status Coming Soon (Akan Datang)!" 
            : "Episod {$episode->episode_number} (\"{$episode->title}\") berjaya ditambah dan diterbitkan!";

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', $statusMsg);
    }

    /**
     * Show form to edit an existing episode or attach video to coming soon episode.
     */
    public function edit(Drama $drama, Episode $episode): View
    {
        return view('creator.episodes.edit', [
            'drama' => $drama,
            'episode' => $episode,
        ]);
    }

    /**
     * Update an existing episode.
     */
    public function update(Request $request, Drama $drama, Episode $episode): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'episode_number' => 'required|integer|min:1',
            'video_type' => 'required|in:storage,youtube,coming_soon',
            'youtube_url' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'video_file' => 'nullable|file|mimes:mp4,webm,mov,m4v|max:204800',
            'duration' => 'nullable|integer|min:5|max:7200',
            'is_free_preview' => 'nullable',
        ], [
            'title.required' => 'Tajuk episod wajib diisi.',
            'episode_number.required' => 'Nombor episod wajib diisi.',
        ]);

        $videoType = $validated['video_type'];
        $youtubeVideoId = null;
        $videoUrl = null;
        $videoSource = 'youtube';
        $isComingSoon = false;

        if ($videoType === 'coming_soon') {
            $isComingSoon = true;
        } elseif ($videoType === 'youtube') {
            $rawUrl = $validated['youtube_url'] ?? '';
            $youtubeVideoId = \App\Helpers\MediaHelper::extractYouTubeId($rawUrl);
            if (!empty($youtubeVideoId)) {
                $videoUrl = \App\Helpers\MediaHelper::cleanYouTubeUrl($rawUrl) ?? $rawUrl;
                $videoSource = 'youtube';
            } else {
                $isComingSoon = true;
            }
        } else {
            if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
                $file = $request->file('video_file');
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = 'ep_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetDir = public_path('uploads/videos');
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                $file->move($targetDir, $filename);
                $videoUrl = '/uploads/videos/' . $filename;
                $videoSource = 'direct';
            } elseif (!empty($validated['video_url'])) {
                $videoUrl = trim($validated['video_url']);
                $videoSource = 'direct';
            } elseif (!empty($episode->video_url) && !$episode->isComingSoon()) {
                // Keep existing video URL if no new one provided
                $videoUrl = $episode->video_url;
                $videoSource = 'direct';
            } else {
                $isComingSoon = true;
                $videoSource = 'direct';
            }
        }

        $episode->update([
            'title' => $validated['title'],
            'episode_number' => $validated['episode_number'],
            'video_type' => $isComingSoon ? 'storage' : $videoType,
            'video_source' => $videoSource,
            'youtube_video_id' => $youtubeVideoId,
            'video_url' => $videoUrl,
            'duration' => $validated['duration'] ?? ($episode->duration ?? 60),
            'is_free_preview' => $request->boolean('is_free_preview'),
            'is_coming_soon' => $isComingSoon,
        ]);

        $statusText = $isComingSoon ? 'Akan Datang (Coming Soon)' : 'Telah Diterbitkan dengan Video';

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', "Episod {$episode->episode_number} (\"{$episode->title}\") berjaya dikemaskini [Status: {$statusText}]!");
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
