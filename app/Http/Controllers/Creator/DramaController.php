<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Drama;
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

        return view('creator.dramas.edit', [
            'drama' => $drama,
            'availableGenres' => $availableGenres,
            'availableRatings' => $availableRatings,
            'commonWarnings' => $commonWarnings,
        ]);
    }

    /**
     * Update Drama Series Main Details (14 Features).
     */
    public function update(Request $request, Drama $drama): RedirectResponse
    {
        $this->authorizeDrama($drama);

        $validated = $request->validate([
            // 4. Tajuk & Slug
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:dramas,slug,' . $drama->id],

            // 5. Sinopsis Penuh
            'synopsis' => ['nullable', 'string'],

            // 1. Poster & Banner
            'poster_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'banner_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'poster_url' => ['nullable', 'string', 'max:255'],
            'banner_url' => ['nullable', 'string', 'max:255'],

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
            'release_status' => ['required', 'in:ongoing,completed,hiatus,upcoming'],

            // 8. Pautan Video Trailer
            'trailer_url' => ['nullable', 'string', 'max:500'],

            // 9. Senarai Pelakon Utama
            'cast_raw' => ['nullable', 'string'],

            // 10. Amaran Kandungan & Rating Umur
            'age_rating' => ['required', 'string', 'max:20'],
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
            'slug.unique' => 'Slug URL ini telah digunakan oleh drama lain.',
            'poster_file.max' => 'Saiz poster maksimum ialah 5MB.',
            'banner_file.max' => 'Saiz banner maksimum ialah 8MB.',
            'release_status.required' => 'Sila pilih status tayangan siri.',
        ]);

        // 4. Title & Slug
        $drama->title = $validated['title'];
        if (!empty($validated['slug'])) {
            $drama->slug = Str::slug($validated['slug']);
        } elseif (empty($drama->slug)) {
            $drama->slug = Str::slug($validated['title']);
        }

        // 5. Synopsis
        $drama->synopsis = $validated['synopsis'] ?? null;

        // 1. Poster File Upload
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

        // 1. Banner File Upload
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

        // 7. Status Tayangan Siri
        $drama->release_status = $validated['release_status'];

        // 8. Trailer URL
        $drama->trailer_url = !empty($validated['trailer_url']) ? trim($validated['trailer_url']) : null;

        // 9. Senarai Pelakon Utama (parse line by line or JSON)
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
        } else {
            $drama->cast = null;
        }

        // 10. Amaran Kandungan & Rating Umur
        $drama->age_rating = $validated['age_rating'] ?? '13+';
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

        return redirect()
            ->route('creator.dramas.manage', $drama)
            ->with('success', "Butiran siri drama \"{$drama->title}\" berjaya dikemaskini sepenuhnya!");
    }
}
