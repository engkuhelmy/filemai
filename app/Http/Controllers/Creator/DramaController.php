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
}
