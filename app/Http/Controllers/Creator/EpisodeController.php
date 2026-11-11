<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEpisodeRequest;
use App\Http\Requests\UpdateEpisodeRequest;
use App\Models\Drama;
use App\Models\Episode;
use App\Helpers\MediaHelper;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EpisodeController extends Controller
{
    /**
     * Authorize user access to manage this drama.
     */
    protected function authorizeDrama(Drama $drama): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Sila log masuk untuk mengakses studio pencipta.');
        }

        if ($user->isAdmin()) {
            return;
        }

        if ((int)$drama->creator_id !== (int)$user->id) {
            abort(403, 'Anda tidak mempunyai kebenaran untuk menguruskan siri drama ini.');
        }
    }

    /**
     * Authorize user access to manage this episode.
     */
    protected function authorizeEpisode(Drama $drama, Episode $episode): void
    {
        $this->authorizeDrama($drama);

        if ((int)$episode->drama_id !== (int)$drama->id) {
            abort(404, 'Episod tidak sah untuk siri drama ini.');
        }
    }

    /**
     * Feature 1: Episode Dashboard (Senarai Episod).
     */
    public function index(Drama $drama): View
    {
        $this->authorizeDrama($drama);

        $episodes = $drama->episodes()
            ->orderBy('episode_number', 'asc')
            ->get();

        $stats = [
            'total' => $episodes->count(),
            'published' => $episodes->where('status', 'published')->count(),
            'draft' => $episodes->where('status', 'draft')->count(),
            'scheduled' => $episodes->where('status', 'scheduled')->count(),
            'free' => $episodes->where('is_free_preview', true)->count(),
            'vip' => $episodes->where('is_free_preview', false)->count(),
            'views' => $episodes->sum('views_count'),
        ];

        return view('creator.episodes.index', [
            'drama' => $drama,
            'episodes' => $episodes,
            'stats' => $stats,
        ]);
    }

    /**
     * Show form for creating a new episode.
     */
    public function create(Drama $drama): View
    {
        $this->authorizeDrama($drama);

        // Suggest the next sequential episode number
        $nextEpNumber = ($drama->episodes()->max('episode_number') ?? 0) + 1;

        return view('creator.episodes.create', [
            'drama' => $drama,
            'nextEpNumber' => $nextEpNumber,
            'existingEpisodes' => $drama->episodes()->orderBy('episode_number', 'asc')->get(),
        ]);
    }

    /**
     * Store newly created episode for the drama.
     */
    public function store(StoreEpisodeRequest $request, Drama $drama): RedirectResponse
    {
        $this->authorizeDrama($drama);

        $validated = $request->validated();
        $videoType = $validated['video_type'] ?? 'storage';

        $youtubeVideoId = null;
        $videoUrl = null;
        $videoSource = 'direct';

        if ($videoType === 'youtube') {
            $rawUrl = $validated['youtube_url'] ?? '';
            $youtubeVideoId = MediaHelper::extractYouTubeId($rawUrl);
            $videoUrl = MediaHelper::cleanYouTubeUrl($rawUrl) ?? $rawUrl;
            $videoSource = 'youtube';
        } else {
            // Storage file upload or CDN URL
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
            } elseif (!empty($validated['video_url'])) {
                $videoUrl = trim($validated['video_url']);
            } else {
                $videoUrl = '/uploads/videos/sample_reel.mp4';
            }

            $videoSource = 'direct';
            $youtubeVideoId = null;
        }

        // Feature 4: Handle Custom Thumbnail Upload
        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail_file') && $request->file('thumbnail_file')->isValid()) {
            $thumbFile = $request->file('thumbnail_file');
            $thumbExt = strtolower($thumbFile->getClientOriginalExtension());
            $thumbFilename = 'thumb_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $thumbExt;

            $thumbDir = public_path('uploads/thumbnails');
            if (!file_exists($thumbDir)) {
                @mkdir($thumbDir, 0755, true);
            }

            $thumbFile->move($thumbDir, $thumbFilename);
            $thumbnailUrl = '/uploads/thumbnails/' . $thumbFilename;
        } elseif (!empty($validated['thumbnail_url'])) {
            $thumbnailUrl = trim($validated['thumbnail_url']);
        }

        // Feature 5: Status and Scheduled Date
        $status = $validated['status'] ?? 'published';
        $scheduledAt = null;
        if ($status === 'scheduled' && !empty($validated['scheduled_at'])) {
            $scheduledAt = Carbon::parse($validated['scheduled_at']);
        }

        // Create episode record
        $episode = $drama->episodes()->create([
            'episode_number' => $validated['episode_number'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'video_type' => $videoType,
            'video_source' => $videoSource,
            'youtube_video_id' => $youtubeVideoId,
            'video_url' => $videoUrl,
            'thumbnail_url' => $thumbnailUrl,
            'duration' => $validated['duration'] ?? 60,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'is_free_preview' => $request->boolean('is_free_preview', false),
        ]);

        return redirect()
            ->route('creator.dramas.episodes.index', $drama)
            ->with('success', "Episod {$episode->episode_number} (\"{$episode->title}\") berjaya ditambah!");
    }

    /**
     * Feature 2 & 3: Show form for editing an existing episode.
     */
    public function edit(Drama $drama, Episode $episode): View
    {
        $this->authorizeEpisode($drama, $episode);

        return view('creator.episodes.edit', [
            'drama' => $drama,
            'episode' => $episode,
        ]);
    }

    /**
     * Feature 2 & 3: Update existing episode metadata, media source, thumbnail, and visibility.
     */
    public function update(UpdateEpisodeRequest $request, Drama $drama, Episode $episode): RedirectResponse
    {
        $this->authorizeEpisode($drama, $episode);

        $validated = $request->validated();
        $videoTypeChoice = $validated['video_type'] ?? 'current';

        // Feature 3: Swap Media Source
        if ($videoTypeChoice === 'youtube') {
            $rawUrl = $validated['youtube_url'] ?? '';
            $episode->video_type = 'youtube';
            $episode->video_source = 'youtube';
            $episode->youtube_video_id = MediaHelper::extractYouTubeId($rawUrl);
            $episode->video_url = MediaHelper::cleanYouTubeUrl($rawUrl) ?? $rawUrl;
        } elseif ($videoTypeChoice === 'storage') {
            $episode->video_type = 'storage';
            $episode->video_source = 'direct';
            $episode->youtube_video_id = null;

            if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
                $file = $request->file('video_file');
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = 'ep_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

                $targetDir = public_path('uploads/videos');
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }

                $file->move($targetDir, $filename);
                $episode->video_url = '/uploads/videos/' . $filename;
            } elseif (!empty($validated['video_url'])) {
                $episode->video_url = trim($validated['video_url']);
            }
        }
        // If 'current', keep existing video fields intact

        // Feature 4: Custom Thumbnail
        if ($request->boolean('remove_custom_thumbnail')) {
            $episode->thumbnail_url = null;
        } elseif ($request->hasFile('thumbnail_file') && $request->file('thumbnail_file')->isValid()) {
            $thumbFile = $request->file('thumbnail_file');
            $thumbExt = strtolower($thumbFile->getClientOriginalExtension());
            $thumbFilename = 'thumb_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $thumbExt;

            $thumbDir = public_path('uploads/thumbnails');
            if (!file_exists($thumbDir)) {
                @mkdir($thumbDir, 0755, true);
            }

            $thumbFile->move($thumbDir, $thumbFilename);
            $episode->thumbnail_url = '/uploads/thumbnails/' . $thumbFilename;
        } elseif (!empty($validated['thumbnail_url'])) {
            $episode->thumbnail_url = trim($validated['thumbnail_url']);
        }

        // Feature 2: Metadata (Title, Episode Number, Description, Duration)
        $episode->episode_number = (int)$validated['episode_number'];
        $episode->title = $validated['title'];
        $episode->description = $validated['description'] ?? null;
        $episode->duration = $validated['duration'] ?? $episode->duration ?? 60;

        // Feature 5: Visibility Status & Scheduling
        $episode->status = $validated['status'] ?? 'published';
        if ($episode->status === 'scheduled' && !empty($validated['scheduled_at'])) {
            $episode->scheduled_at = Carbon::parse($validated['scheduled_at']);
        } elseif ($episode->status !== 'scheduled') {
            $episode->scheduled_at = null;
        }

        // Feature 6: Paywall / Free Access Toggle
        $episode->is_free_preview = $request->boolean('is_free_preview', false);

        $episode->save();

        return redirect()
            ->route('creator.dramas.episodes.index', $drama)
            ->with('success', "Episod {$episode->episode_number} (\"{$episode->title}\") berjaya dikemaskini!");
    }

    /**
     * Delete an episode.
     */
    public function destroy(Drama $drama, Episode $episode): RedirectResponse
    {
        $this->authorizeEpisode($drama, $episode);

        $epNum = $episode->episode_number;
        $episode->delete();

        return redirect()
            ->route('creator.dramas.episodes.index', $drama)
            ->with('success', "Episod #{$epNum} berjaya dipadam.");
    }

    /**
     * Feature 6: Quick Toggle Free / VIP Access (One-click toggle).
     */
    public function toggleFree(Request $request, Drama $drama, Episode $episode): JsonResponse|RedirectResponse
    {
        $this->authorizeEpisode($drama, $episode);

        $episode->is_free_preview = !$episode->is_free_preview;
        $episode->save();

        $label = $episode->is_free_preview ? 'Percuma (Iklan)' : 'VIP Sahaja';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_free_preview' => $episode->is_free_preview,
                'label' => $label,
                'message' => "Akses episod #{$episode->episode_number} ditukar ke {$label}.",
            ]);
        }

        return back()->with('success', "Akses episod #{$episode->episode_number} ditukar ke {$label}.");
    }

    /**
     * Feature 5: Quick Toggle Status (Publish / Draft).
     */
    public function toggleStatus(Request $request, Drama $drama, Episode $episode): JsonResponse|RedirectResponse
    {
        $this->authorizeEpisode($drama, $episode);

        $newStatus = $request->input('status');
        if (!in_array($newStatus, ['published', 'draft', 'scheduled'])) {
            $newStatus = ($episode->status === 'published') ? 'draft' : 'published';
        }

        $episode->status = $newStatus;
        if ($newStatus !== 'scheduled') {
            $episode->scheduled_at = null;
        }
        $episode->save();

        $statusLabels = [
            'published' => 'Diterbitkan',
            'draft' => 'Draf',
            'scheduled' => 'Dijadualkan',
        ];

        $label = $statusLabels[$newStatus] ?? $newStatus;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $episode->status,
                'label' => $label,
                'message' => "Status episod #{$episode->episode_number} ditukar ke {$label}.",
            ]);
        }

        return back()->with('success', "Status episod #{$episode->episode_number} ditukar ke {$label}.");
    }

    /**
     * Feature 7: Drag-and-Drop Reordering of Episodes.
     */
    public function reorder(Request $request, Drama $drama): JsonResponse
    {
        $this->authorizeDrama($drama);

        $order = $request->input('order');
        if (!is_array($order) || empty($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Susunan data tidak sah.',
            ], 422);
        }

        // Verify all IDs belong to this drama
        $validIds = $drama->episodes()->pluck('id')->toArray();
        foreach ($order as $id) {
            if (!in_array((int)$id, $validIds, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat episod yang tidak tergolong dalam siri drama ini.',
                ], 403);
            }
        }

        // Update sequentially within a transaction
        DB::transaction(function () use ($order, $drama) {
            // First pass: shift numbers by high offset to avoid unique constraint collisions
            foreach ($order as $index => $id) {
                Episode::where('id', $id)
                    ->where('drama_id', $drama->id)
                    ->update(['episode_number' => 10000 + $index + 1]);
            }

            // Second pass: set sequential 1, 2, 3...
            foreach ($order as $index => $id) {
                Episode::where('id', $id)
                    ->where('drama_id', $drama->id)
                    ->update(['episode_number' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Susunan episod berjaya dikemaskini!',
        ]);
    }

    /**
     * Data Extraction & Auto-Fill from YouTube:
     * Extracts title, synopsis/description, duration (in seconds), and highest resolution thumbnail.
     */
    public function extractYouTube(Request $request): JsonResponse
    {
        $url = $request->input('url') ?? $request->input('youtube_url');
        if (empty($url) || !is_string($url)) {
            return response()->json([
                'success' => false,
                'message' => 'Sila masukkan pautan URL YouTube yang sah.',
            ], 422);
        }

        $data = YouTubeService::extractVideoData($url);

        if (empty($data['success'])) {
            return response()->json([
                'success' => false,
                'message' => $data['message'] ?? 'Gagal memuat turun data video. Pastikan pautan sah dan video adalah Public/Unlisted.',
            ], 422);
        }

        return response()->json($data);
    }
}
