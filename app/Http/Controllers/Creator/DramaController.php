<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Drama;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class DramaController extends Controller
{
    /**
     * Show creator studio dashboard with drama listings.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        // If admin, show all dramas. If creator, show their dramas (or all if none created yet)
        $query = Drama::with('creator')->withCount('episodes')->orderBy('created_at', 'desc');
        
        if (!$user->isAdmin()) {
            $userDramas = Drama::where('creator_id', $user->id)->withCount('episodes')->orderBy('created_at', 'desc')->get();
            $dramas = $userDramas->isNotEmpty() ? $userDramas : $query->get();
        } else {
            $dramas = $query->get();
        }

        $totalViews = $dramas->sum('views_count');
        $totalEpisodes = $dramas->sum('episodes_count');

        return view('creator.dashboard', [
            'dramas' => $dramas,
            'totalViews' => $totalViews,
            'totalEpisodes' => $totalEpisodes,
        ]);
    }

    /**
     * Show form to create a new drama series with upfront episodes list.
     */
    public function create(): View
    {
        $genres = ['Romance', 'Action', 'Thriller', 'Revenge', 'CEO', 'Drama', 'Komedi', 'Fantasi', 'Misteri'];

        return view('creator.dramas.create', [
            'genres' => $genres,
        ]);
    }

    /**
     * Store newly created drama and its initial list of episodes.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'type' => 'nullable|in:series,movie',
            'synopsis' => 'nullable|string',
            'poster_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120',
            'poster_url' => 'nullable|string|max:500',
            'banner_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120',
            'banner_url' => 'nullable|string|max:500',
            'episodes' => 'nullable|array',
            'episodes.*.episode_number' => 'nullable|integer|min:1',
            'episodes.*.title' => 'nullable|string|max:255',
            'episodes.*.video_type' => 'nullable|string',
            'episodes.*.youtube_url' => 'nullable|string',
            'episodes.*.video_url' => 'nullable|string',
            'episodes.*.duration' => 'nullable|integer|min:5|max:7200',
            'episodes.*.is_free_preview' => 'nullable',
        ], [
            'title.required' => 'Tajuk drama wajib diisi.',
            'genre.required' => 'Sila pilih genre drama.',
            'poster_file.mimes' => 'Format poster mestilah JPG, PNG, atau WebP.',
            'poster_file.max' => 'Saiz poster maksimum ialah 5MB.',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($validated['title']);
        if (empty($baseSlug)) {
            $baseSlug = 'drama-' . time();
        }
        $slug = $baseSlug;
        $counter = 1;
        while (Drama::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Handle Poster upload or URL
        $posterUrl = '/uploads/posters/jodoh-kontrak-ceo.jpg';
        if ($request->hasFile('poster_file') && $request->file('poster_file')->isValid()) {
            $file = $request->file('poster_file');
            $filename = 'poster_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/posters');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            $file->move($destDir, $filename);
            $posterUrl = '/uploads/posters/' . $filename;
        } elseif (!empty($validated['poster_url'])) {
            $posterUrl = trim($validated['poster_url']);
        }

        // Handle Banner upload or URL
        $bannerUrl = $posterUrl;
        if ($request->hasFile('banner_file') && $request->file('banner_file')->isValid()) {
            $file = $request->file('banner_file');
            $filename = 'banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/posters');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            $file->move($destDir, $filename);
            $bannerUrl = '/uploads/posters/' . $filename;
        } elseif (!empty($validated['banner_url'])) {
            $bannerUrl = trim($validated['banner_url']);
        }

        $creatorId = auth()->id() ?? 2;

        // Create Drama Record
        $drama = Drama::create([
            'creator_id' => $creatorId,
            'title' => trim($validated['title']),
            'slug' => $slug,
            'synopsis' => trim($validated['synopsis'] ?? ''),
            'genre' => $validated['genre'],
            'type' => $validated['type'] ?? 'series',
            'status' => 'published',
            'poster_url' => $posterUrl,
            'banner_url' => $bannerUrl,
            'views_count' => 0,
        ]);

        // Process upfront episodes list
        $episodesData = $request->input('episodes', []);
        $createdCount = 0;
        $comingSoonCount = 0;

        if (is_array($episodesData) && count($episodesData) > 0) {
            foreach ($episodesData as $index => $epItem) {
                if (empty($epItem) || !is_array($epItem)) {
                    continue;
                }

                $epNumber = !empty($epItem['episode_number']) ? (int)$epItem['episode_number'] : ($index + 1);
                $epTitle = !empty(trim($epItem['title'] ?? '')) ? trim($epItem['title']) : "Episod {$epNumber}";
                $epDuration = !empty($epItem['duration']) ? (int)$epItem['duration'] : 60;
                $isFree = !empty($epItem['is_free_preview']) && ($epItem['is_free_preview'] == '1' || $epItem['is_free_preview'] === true || $epItem['is_free_preview'] === 'on');

                $ytUrl = trim($epItem['youtube_url'] ?? '');
                $directUrl = trim($epItem['video_url'] ?? '');
                $videoTypeSelection = $epItem['video_type'] ?? 'youtube';

                $youtubeVideoId = null;
                $finalVideoUrl = null;
                $videoSource = 'youtube';
                $videoType = 'youtube';
                $isComingSoon = false;

                // Check if video is provided
                if ($videoTypeSelection === 'youtube' && !empty($ytUrl)) {
                    $youtubeVideoId = \App\Helpers\MediaHelper::extractYouTubeId($ytUrl);
                    if (!empty($youtubeVideoId)) {
                        $finalVideoUrl = \App\Helpers\MediaHelper::cleanYouTubeUrl($ytUrl) ?? $ytUrl;
                        $videoSource = 'youtube';
                        $videoType = 'youtube';
                    } else {
                        // Invalid YouTube URL, automatically treated as coming soon
                        $isComingSoon = true;
                    }
                } elseif ($videoTypeSelection === 'storage' && !empty($directUrl)) {
                    $finalVideoUrl = $directUrl;
                    $videoSource = 'direct';
                    $videoType = 'storage';
                } else {
                    // No video provided -> AUTOMATICALLY COMING SOON!
                    $isComingSoon = true;
                }

                if ($isComingSoon) {
                    $comingSoonCount++;
                    $finalVideoUrl = null;
                    $youtubeVideoId = null;
                } else {
                    $createdCount++;
                }

                $drama->episodes()->create([
                    'episode_number' => $epNumber,
                    'title' => $epTitle,
                    'video_type' => $videoType,
                    'video_source' => $videoSource,
                    'youtube_video_id' => $youtubeVideoId,
                    'video_url' => $finalVideoUrl,
                    'duration' => $epDuration,
                    'is_free_preview' => $isFree,
                    'is_coming_soon' => $isComingSoon,
                ]);
            }
        }

        $totalEps = $createdCount + $comingSoonCount;
        $msg = "Siri drama '{$drama->title}' berjaya dicipta!";
        if ($totalEps > 0) {
            $msg .= " Sebanyak {$totalEps} episod didaftarkan ({$comingSoonCount} berstatus Coming Soon).";
        }

        return redirect()->route('creator.dramas.manage', $drama)->with('success', $msg);
    }

    /**
     * Manage a specific drama and all its episodes.
     */
    public function manage(Drama $drama): View
    {
        $episodes = $drama->episodes()->orderBy('episode_number', 'asc')->get();

        return view('creator.dramas.manage', [
            'drama' => $drama,
            'episodes' => $episodes,
        ]);
    }

    /**
     * Show form to edit drama details.
     * Poster and title are only editable within 24 hours of creation.
     */
    public function edit(Drama $drama): View
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'isAdmin') && !$user->isAdmin() && $drama->creator_id && $drama->creator_id !== $user->id) {
            abort(403, 'Anda tidak mempunyai kebenaran untuk menguruskan siri drama ini.');
        }

        $genres = ['Romance', 'Action', 'Thriller', 'Revenge', 'CEO', 'Drama', 'Komedi', 'Fantasi', 'Misteri'];

        return view('creator.dramas.edit', [
            'drama' => $drama,
            'genres' => $genres,
            'canEditIdentity' => $drama->canEditIdentity($user),
            'remainingTime' => $drama->remainingEditTime(),
        ]);
    }

    /**
     * Update drama details.
     * Enforces the 24-hour rule: poster and title can only be changed within 24 hours of creation.
     */
    public function update(Request $request, Drama $drama): RedirectResponse
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'isAdmin') && !$user->isAdmin() && $drama->creator_id && $drama->creator_id !== $user->id) {
            abort(403, 'Anda tidak mempunyai kebenaran untuk mengemaskini siri drama ini.');
        }

        $canEditIdentity = $drama->canEditIdentity($user);

        $rules = [
            'genre' => 'required|string|max:100',
            'type' => 'nullable|in:series,movie',
            'status' => 'nullable|in:published,draft,completed',
            'synopsis' => 'nullable|string',
        ];

        if ($canEditIdentity) {
            $rules['title'] = 'required|string|max:255';
            $rules['poster_file'] = 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120';
            $rules['poster_url'] = 'nullable|string|max:500';
            $rules['banner_file'] = 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120';
            $rules['banner_url'] = 'nullable|string|max:500';
        } else {
            $rules['title'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules, [
            'title.required' => 'Tajuk drama wajib diisi.',
            'genre.required' => 'Sila pilih genre drama.',
            'poster_file.mimes' => 'Format poster mestilah JPG, PNG, atau WebP.',
            'poster_file.max' => 'Saiz poster maksimum ialah 5MB.',
        ]);

        $attemptedIdentityChange = false;

        // Check and apply Title change (24-hour rule)
        if ($canEditIdentity && !empty($validated['title'])) {
            $newTitle = trim($validated['title']);
            if ($newTitle !== $drama->title) {
                $drama->title = $newTitle;
                $baseSlug = Str::slug($newTitle);
                if (empty($baseSlug)) {
                    $baseSlug = 'drama-' . $drama->id;
                }
                $slug = $baseSlug;
                $counter = 1;
                while (Drama::where('slug', $slug)->where('id', '!=', $drama->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $drama->slug = $slug;
            }
        } elseif (!$canEditIdentity && $request->filled('title') && trim($request->input('title')) !== $drama->title) {
            $attemptedIdentityChange = true;
        }

        // Check and apply Poster change (24-hour rule)
        if ($canEditIdentity) {
            if ($request->hasFile('poster_file') && $request->file('poster_file')->isValid()) {
                $file = $request->file('poster_file');
                $filename = 'poster_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $destDir = public_path('uploads/posters');
                if (!file_exists($destDir)) {
                    @mkdir($destDir, 0775, true);
                }
                $file->move($destDir, $filename);
                $drama->poster_url = '/uploads/posters/' . $filename;
            } elseif (!empty($validated['poster_url'])) {
                $drama->poster_url = trim($validated['poster_url']);
            }

            if ($request->hasFile('banner_file') && $request->file('banner_file')->isValid()) {
                $file = $request->file('banner_file');
                $filename = 'banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $destDir = public_path('uploads/posters');
                if (!file_exists($destDir)) {
                    @mkdir($destDir, 0775, true);
                }
                $file->move($destDir, $filename);
                $drama->banner_url = '/uploads/posters/' . $filename;
            } elseif (!empty($validated['banner_url'])) {
                $drama->banner_url = trim($validated['banner_url']);
            }
        } elseif (!$canEditIdentity && ($request->hasFile('poster_file') || $request->hasFile('banner_file'))) {
            $attemptedIdentityChange = true;
        }

        // Always update metadata
        $drama->synopsis = trim($validated['synopsis'] ?? '');
        $drama->genre = $validated['genre'];
        $drama->type = $validated['type'] ?? $drama->type ?? 'series';
        if (!empty($validated['status'])) {
            $drama->status = $validated['status'];
        }

        $drama->save();

        if ($attemptedIdentityChange) {
            return redirect()
                ->route('creator.dramas.manage', $drama)
                ->with('info', "Maklumat siri drama dikemaskini. Perhatian: Tajuk & poster TIDAK diubah kerana peraturan 24 jam (hanya boleh ditukar dalam tempoh 24 jam selepas dicipta).");
        }

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', "Maklumat siri drama '{$drama->title}' berjaya dikemaskini!");
    }
}
