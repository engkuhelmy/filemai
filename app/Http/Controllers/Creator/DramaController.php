<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Drama;
use App\Models\Episode;
use App\Models\CreatorWallet;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class DramaController extends Controller
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

        $wallet = CreatorWallet::forUser($user->id);
        $ratePerBlock = SystemSetting::get('creator_earnings_rate_per_block', 0.000100);

        return view('creator.dashboard', [
            'dramas' => $dramas,
            'totalViews' => $totalViews,
            'totalEpisodes' => $totalEpisodes,
            'wallet' => $wallet,
            'ratePerBlock' => $ratePerBlock,
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
        $this->processImageInput($request, 'poster_file', 'poster_url', 'poster');
        $this->processImageInput($request, 'banner_file', 'banner_url', 'banner');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'type' => 'nullable|in:drama,series,short_film,film,movie',
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

        $type = $validated['type'] ?? 'series';
        if (in_array($type, ['series', 'drama'])) {
            $type = 'drama';
        }

        $drama = Drama::create([
            'creator_id' => auth()->id() ?? 2,
            'title' => trim($validated['title']),
            'slug' => $slug,
            'genre' => $validated['genre'],
            'type' => $type,
            'synopsis' => trim($validated['synopsis'] ?? ''),
            'poster_url' => $posterUrl,
            'banner_url' => $bannerUrl,
            'status' => 'published',
            'views_count' => 0,
            'featured' => false,
        ]);

        // Bulk insert initial episodes if provided from wizard
        if (!empty($validated['episodes']) && is_array($validated['episodes'])) {
            foreach ($validated['episodes'] as $epData) {
                if (empty($epData['title']) && empty($epData['episode_number'])) {
                    continue;
                }

                $epNum = !empty($epData['episode_number']) ? (int)$epData['episode_number'] : 1;
                $epTitle = !empty($epData['title']) ? trim($epData['title']) : "Episod {$epNum}";
                $epType = $epData['video_type'] ?? 'storage';
                $isFree = !empty($epData['is_free_preview']);
                $duration = !empty($epData['duration']) ? (int)$epData['duration'] : 60;

                $youtubeId = null;
                $videoUrl = null;
                $videoSource = 'youtube';
                $isComingSoon = false;

                if ($epType === 'coming_soon') {
                    $isComingSoon = true;
                    $videoSource = 'storage';
                } elseif ($epType === 'youtube') {
                    $rawUrl = $epData['youtube_url'] ?? '';
                    $youtubeId = \App\Helpers\MediaHelper::extractYouTubeId($rawUrl);
                    if (!empty($youtubeId)) {
                        $videoUrl = \App\Helpers\MediaHelper::cleanYouTubeUrl($rawUrl) ?? $rawUrl;
                        $videoSource = 'youtube';
                    } else {
                        $isComingSoon = true;
                    }
                } else {
                    $videoUrl = !empty($epData['video_url']) ? trim($epData['video_url']) : null;
                    $videoSource = 'direct';
                    if (empty($videoUrl)) {
                        $isComingSoon = true;
                    }
                }

                Episode::create([
                    'drama_id' => $drama->id,
                    'episode_number' => $epNum,
                    'title' => $epTitle,
                    'video_type' => $epType === 'youtube' ? 'youtube' : 'storage',
                    'video_source' => $videoSource,
                    'youtube_video_id' => $youtubeId,
                    'video_url' => $videoUrl,
                    'duration' => $duration,
                    'views_count' => 0,
                    'is_free_preview' => $isFree,
                    'is_coming_soon' => $isComingSoon,
                ]);
            }
        }

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', "Siri drama '{$drama->title}' berjaya dicipta! Sila semak senarai episod.");
    }

    /**
     * Manage an existing drama and its episodes.
     */
    public function manage(Drama $drama): View
    {
        $this->authorizeDrama($drama);

        $drama->loadCount('episodes');
        $episodes = $drama->episodes()->orderBy('episode_number', 'asc')->get();

        return view('creator.dramas.manage', [
            'drama' => $drama,
            'episodes' => $episodes,
        ]);
    }

    /**
     * Show form for editing the Drama Series Main Details (14 Features).
     */
    public function edit(Drama $drama): View
    {
        $this->authorizeDrama($drama);

        $availableGenres = [
            'Romance', 'Action', 'Thriller', 'CEO', 'Revenge', 
            'Drama', 'Komedi', 'Keluarga', 'Fantasi', 'Seram', 'Misteri'
        ];

        $availableRatings = ['U', '13+', '16+', '18+'];

        $commonWarnings = [
            'Keganasan Ringan', 'Konflik Emosi', 'Babak Romantik', 
            'Bahasa Kasar Ringan', 'Isu Kekeluargaan', 'Buli / Dendam'
        ];

        $canEditIdentity = $drama->canEditIdentity(auth()->user());
        $remainingTime = $drama->remainingEditTime();

        return view('creator.dramas.edit', [
            'drama' => $drama,
            'availableGenres' => $availableGenres,
            'availableRatings' => $availableRatings,
            'commonWarnings' => $commonWarnings,
            'canEditIdentity' => $canEditIdentity,
            'remainingTime' => $remainingTime,
        ]);
    }

    /**
     * Update Drama Series Main Details (14 Features + 24-Hour Edit Rule Check).
     */
    public function update(Request $request, Drama $drama): RedirectResponse
    {
        $this->authorizeDrama($drama);

        $this->processImageInput($request, 'poster_file', 'poster_url', 'poster');
        $this->processImageInput($request, 'banner_file', 'banner_url', 'banner');

        $validated = $request->validate([
            // 4. Tajuk & Slug
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:dramas,slug,' . $drama->id],

            // Format Kandungan (Tagging: Drama, Short Film, Filem)
            'type' => ['required', 'string', 'in:drama,series,short_film,film,movie'],

            // 5. Sinopsis Penuh
            'synopsis' => ['nullable', 'string'],

            // 1. Poster & Banner
            'poster_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'banner_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'poster_url' => ['nullable', 'string', 'max:500'],
            'banner_url' => ['nullable', 'string', 'max:500'],

            // 2. Tetapan SEO & Keywords
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:500'],

            // 3. Maklumat & Profil Creator
            'creator_bio' => ['nullable', 'string', 'max:2000'],

            // 6. Kategori & Tag Genre
            'genre' => ['nullable', 'string', 'max:100'],
            'genres' => ['nullable', 'array'],

            // 7. Status Tayangan Siri
            'release_status' => ['nullable', 'in:ongoing,completed,hiatus,upcoming'],
            'status' => ['nullable', 'in:published,draft,archived'],

            // 8. Pautan Video Trailer
            'trailer_url' => ['nullable', 'string', 'max:500'],

            // 9. Senarai Pelakon Utama
            'cast_raw' => ['nullable', 'string'],

            // 10. Amaran Kandungan & Rating Umur
            'age_rating' => ['nullable', 'string', 'max:20'],
            'content_warnings' => ['nullable', 'array'],

            // 11. Papan Pengumuman Creator
            'announcement' => ['nullable', 'string', 'max:500'],

            // 12. Harga Pakej (Bundle/Season Pricing)
            'season_price' => ['nullable', 'numeric', 'min:0'],
            'is_bundle_enabled' => ['nullable', 'boolean'],
            'bundle_discount_note' => ['nullable', 'string', 'max:255'],

            // 13. Pautan Sosial Media
            'social_hashtag' => ['nullable', 'string', 'max:100'],
            'social_tiktok' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],

            // 14. Tetapan Warna Tema
            'accent_color' => ['nullable', 'string', 'max:30'],
        ], [
            'title.required' => 'Tajuk siri drama wajib diisi.',
            'type.required' => 'Sila pilih format rasmi karya (Drama, Short Film, atau Filem).',
            'slug.unique' => 'Slug URL ini telah digunakan oleh drama lain.',
            'poster_file.max' => 'Saiz poster maksimum ialah 5MB.',
            'banner_file.max' => 'Saiz banner maksimum ialah 8MB.',
        ]);

        $canEditIdentity = $drama->canEditIdentity(auth()->user());
        $attemptedIdentityChange = false;

        // 4. Update Title & Slug (Respect 24-hour rule)
        if ($canEditIdentity) {
            $drama->title = trim($validated['title']);
            if (!empty($validated['slug'])) {
                $drama->slug = Str::slug($validated['slug']);
            } else {
                $baseSlug = Str::slug($validated['title']);
                if (!empty($baseSlug)) {
                    $slug = $baseSlug;
                    $counter = 1;
                    while (Drama::where('slug', $slug)->where('id', '!=', $drama->id)->exists()) {
                        $slug = $baseSlug . '-' . $counter++;
                    }
                    $drama->slug = $slug;
                }
            }
        } elseif ($request->filled('title') && trim($request->input('title')) !== $drama->title) {
            $attemptedIdentityChange = true;
        }

        // Format Kandungan (Tagging: Drama, Short Film, Filem)
        if (!empty($validated['type'])) {
            $drama->type = match(strtolower($validated['type'])) {
                'short_film' => 'short_film',
                'film', 'movie' => 'film',
                default => 'drama',
            };
        }

        // 5. Synopsis
        $drama->synopsis = $validated['synopsis'] ?? null;

        // 1. Poster & Banner File Upload or URL (Respect 24-hour rule)
        if ($canEditIdentity) {
            if ($request->hasFile('poster_file') && $request->file('poster_file')->isValid()) {
                $file = $request->file('poster_file');
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = 'poster_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetDir = public_path('uploads/posters');
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                $file->move($targetDir, $filename);
                $drama->poster_url = '/uploads/posters/' . $filename;
            } elseif (!empty($validated['poster_url'])) {
                $drama->poster_url = trim($validated['poster_url']);
            }

            if ($request->hasFile('banner_file') && $request->file('banner_file')->isValid()) {
                $file = $request->file('banner_file');
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = 'banner_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetDir = public_path('uploads/banners');
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                $file->move($targetDir, $filename);
                $drama->banner_url = '/uploads/banners/' . $filename;
            } elseif (!empty($validated['banner_url'])) {
                $drama->banner_url = trim($validated['banner_url']);
            }
        } elseif ($request->hasFile('poster_file') || $request->hasFile('banner_file')) {
            $attemptedIdentityChange = true;
        }

        // 2. SEO & Keywords
        $drama->meta_title = $validated['meta_title'] ?? null;
        $drama->meta_description = $validated['meta_description'] ?? null;
        $drama->keywords = $validated['keywords'] ?? null;

        // 3. Creator Info & Bio
        $drama->creator_bio = $validated['creator_bio'] ?? null;

        // 6. Kategori & Tag Genre
        $selectedGenres = $request->input('genres', []);
        if (!empty($selectedGenres)) {
            $drama->genres = array_values(array_filter($selectedGenres));
            $drama->genre = $selectedGenres[0];
        } elseif (!empty($validated['genre'])) {
            $drama->genre = $validated['genre'];
            $drama->genres = [$validated['genre']];
        }

        // 7. Status Tayangan Siri & Visibiliti Awam (Sembunyikan jika Tamat/Completed)
        if (!empty($validated['release_status'])) {
            $drama->release_status = $validated['release_status'];
            if ($validated['release_status'] === 'completed') {
                // Makluman: Drama ini akan disembunyikan daripada paparan awam secara automatik
                $drama->status = 'draft';
            } else {
                $drama->status = $validated['status'] ?? 'published';
            }
        } elseif (!empty($validated['status'])) {
            $drama->status = $validated['status'];
        }

        // 8. Trailer URL
        $drama->trailer_url = !empty($validated['trailer_url']) ? trim($validated['trailer_url']) : null;

        // 9. Senarai Pelakon Utama
        if (!empty($validated['cast_raw'])) {
            $lines = explode("\n", str_replace("\r", "", $validated['cast_raw']));
            $castList = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $parts = explode('-', $line, 2);
                $castList[] = [
                    'actor' => trim($parts[0]),
                    'character' => isset($parts[1]) ? trim($parts[1]) : '',
                ];
            }
            $drama->cast = $castList;
        }

        // 10. Amaran Kandungan & Rating Umur
        if (!empty($validated['age_rating'])) {
            $drama->age_rating = $validated['age_rating'];
        }
        $drama->content_warnings = $request->input('content_warnings', []);

        // 11. Papan Pengumuman Creator
        $drama->announcement = !empty($validated['announcement']) ? trim($validated['announcement']) : null;

        // 12. Harga Pakej (Bundle/Season Pricing)
        $drama->season_price = !empty($validated['season_price']) ? (float)$validated['season_price'] : null;
        $drama->is_bundle_enabled = $request->boolean('is_bundle_enabled');
        $drama->bundle_discount_note = $validated['bundle_discount_note'] ?? null;

        // 13. Pautan Sosial Media
        $drama->social_hashtag = !empty($validated['social_hashtag']) ? trim($validated['social_hashtag']) : null;
        $drama->social_links = [
            'tiktok' => !empty($validated['social_tiktok']) ? trim($validated['social_tiktok']) : null,
            'instagram' => !empty($validated['social_instagram']) ? trim($validated['social_instagram']) : null,
        ];

        // 14. Tetapan Warna Tema
        $drama->accent_color = !empty($validated['accent_color']) ? trim($validated['accent_color']) : '#E50914';

        $drama->save();

        if ($attemptedIdentityChange) {
            return redirect()
                ->route('creator.dramas.manage', $drama)
                ->with('info', "Maklumat siri drama dikemaskini. Perhatian: Tajuk & poster TIDAK diubah kerana peraturan 24 jam (hanya boleh ditukar dalam tempoh 24 jam selepas dicipta).");
        }

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', "Butiran siri drama \"{$drama->title}\" berjaya dikemaskini sepenuhnya!");
    }

    /**
     * Delete a drama series and all its associated episodes.
     */
    public function destroy(Request $request, Drama $drama): mixed
    {
        $this->authorizeDrama($drama);

        $title = $drama->title;

        // Clean up tracking and cascade dependencies
        \App\Models\PlaybackProgress::where('drama_id', $drama->id)->delete();
        \App\Models\EpisodeView::where('drama_id', $drama->id)->delete();

        // Delete all episodes and drama
        $drama->episodes()->delete();
        $drama->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Siri drama '{$title}' dan semua episodnya telah dipadam.",
                'redirect_url' => route('creator.dashboard'),
            ]);
        }

        return redirect()
            ->route('creator.dashboard')
            ->with('success', "Siri drama '{$title}' dan semua episodnya telah dipadam.");
    }

    /**
     * Sanitize and process image inputs:
     * - If an image file is uploaded, clear any URL input so it doesn't conflict or fail validation.
     * - If a base64 data URI (data:image/...) is submitted in the URL field, automatically decode and save it as an image file.
     */
    private function processImageInput(Request $request, string $fileKey, string $urlKey, string $prefix): void
    {
        if ($request->hasFile($fileKey) && $request->file($fileKey)->isValid()) {
            $request->merge([$urlKey => null]);
            return;
        }

        $urlValue = $request->input($urlKey);
        if (!empty($urlValue) && is_string($urlValue) && str_starts_with($urlValue, 'data:image/')) {
            if (preg_match('/^data:image\/(\w+);base64,/', $urlValue, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') {
                    $ext = 'jpg';
                }
                if (!in_array($ext, ['jpg', 'png', 'webp'])) {
                    $ext = 'jpg';
                }
                $data = substr($urlValue, strpos($urlValue, ',') + 1);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    $filename = $prefix . '_' . time() . '_' . Str::random(6) . '.' . $ext;
                    $destDir = public_path('uploads/posters');
                    if (!file_exists($destDir)) {
                        @mkdir($destDir, 0775, true);
                    }
                    file_put_contents($destDir . '/' . $filename, $decoded);
                    $request->merge([$urlKey => '/uploads/posters/' . $filename]);
                    return;
                }
            }
            $request->merge([$urlKey => null]);
        }
    }
}
