<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Drama;
use App\Models\Episode;
use App\Http\Controllers\EpisodeWatchController;
use App\Http\Controllers\Creator\EpisodeController as CreatorEpisodeController;
use App\Http\Controllers\Creator\StudioHubController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes - Filemai.my Vertical Streaming Platform
|--------------------------------------------------------------------------
*/

Route::get('/', function (Request $request) {
    $search = trim($request->input('q', ''));
    $selectedGenre = trim($request->input('genre', 'Semua'));
    $activeTab = trim($request->input('tab', 'trends'));

    $query = Drama::where('status', 'published')
        ->with('creator')
        ->withCount('episodes');

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('synopsis', 'LIKE', "%{$search}%");
        });
    }

    if (!empty($selectedGenre) && $selectedGenre !== 'Semua') {
        $query->where('genre', $selectedGenre);
    }

    $dramas = $query->orderBy('views_count', 'desc')->get();

    // Map helper attributes for views
    $dramas->each(function ($d) {
        $d->first_episode_id = $d->episodes()->orderBy('episode_number', 'asc')->value('id') ?? 1;
        $d->rating = ($d->genre === 'Action') ? '8.9' : '8.5';
        $d->hook = ($d->genre === 'Action') ? '🔥 #1 Paling Trending di Malaysia' : '💖 Pilihan Penonton';
        if (empty($d->banner_url)) {
            $d->banner_url = '/uploads/posters/ceo-billboard.jpg';
        }
        $d->trailer_url = '/uploads/videos/sample_reel.mp4';
    });

    // Hero carousel dramas
    $heroDramas = $dramas->take(3)->values();
    if ($heroDramas->isEmpty()) {
        $heroDramas = collect([
            (object) [
                'id' => 2,
                'title' => 'Dendam Pewaris Yang Dibuang',
                'slug' => 'dendam-pewaris-yang-dibuang',
                'synopsis' => 'Dibuang oleh keluarga konglomeratnya 10 tahun lalu dan dihina sebagai pengemis jalanan. Kini, sang naga tidur bangkit kembali sebagai penguasa kewangan global terkuat untuk menuntut setiap titis air mata dan maruah yang telah dirampas!',
                'poster_url' => '/uploads/posters/dendam-pewaris.jpg',
                'banner_url' => '/uploads/posters/dendam-billboard.jpg',
                'trailer_url' => '/uploads/videos/sample_reel.mp4',
                'genre' => 'Action',
                'episodes_count' => 3,
                'first_episode_id' => 5,
                'views_count' => 28922,
                'rating' => '8.9',
                'hook' => '🔥 #1 Paling Trending di Malaysia',
            ],
            (object) [
                'id' => 1,
                'title' => 'Jodoh Kontrak CEO Dingin',
                'slug' => 'jodoh-kontrak-ceo-dingin',
                'synopsis' => 'Seorang gadis biasa terpaksa menandatangani kontrak perkahwinan palsu bernilai RM1 Juta dengan CEO paling dingin di kota demi menyelamatkan perniagaan keluarganya.',
                'poster_url' => '/uploads/posters/jodoh-kontrak-ceo.jpg',
                'banner_url' => '/uploads/posters/ceo-billboard.jpg',
                'trailer_url' => '/uploads/videos/sample_reel.mp4',
                'genre' => 'Romance',
                'episodes_count' => 4,
                'first_episode_id' => 1,
                'views_count' => 15459,
                'rating' => '8.5',
                'hook' => '💖 Top 10 Pilihan Peminat Romance',
            ]
        ]);
    }

    $trendsNow = $dramas;
    $popular = $dramas->sortByDesc('views_count')->values();
    $premieres = $dramas->sortByDesc('id')->values();
    $genres = ['Semua', 'Action', 'Romance', 'Thriller', 'Revenge', 'CEO', 'Drama'];

    return view('welcome', compact('dramas', 'heroDramas', 'trendsNow', 'popular', 'premieres', 'genres', 'selectedGenre', 'activeTab', 'search'));
})->name('home');

// Drama Landing & Main Details Page (Showcasing 14 Features)
Route::get('/drama/{drama:slug}', function (Drama $drama) {
    $episodes = $drama->episodes()
        ->where('status', 'published')
        ->orderBy('episode_number', 'asc')
        ->get();

    return view('dramas.show', [
        'drama' => $drama,
        'episodes' => $episodes,
    ]);
})->name('drama.show');

// Viewer Watch Route (Ad-Gated 9:16 Vertical Player)
Route::get('/drama/{drama:slug}/episode/{episode:episode_number}', [EpisodeWatchController::class, 'show'])
    ->name('episodes.show');

// Alternate direct episode route
Route::get('/watch/{episode}', function (Episode $episode) {
    return redirect()->route('episodes.show', [
        'drama' => $episode->drama->slug,
        'episode' => $episode->episode_number,
    ]);
})->name('watch.direct');

// Creator Studio Episode Management (Protected by auth & creator middleware)
Route::middleware(['auth'])->prefix('creator')->name('creator.')->group(function () {
    
    // Ciri 1 - 15: Creator Studio Hub & Monetization Dashboard
    Route::get('/', [StudioHubController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [StudioHubController::class, 'dashboard'])->name('hub');

    // Ciri 6 & 7: Kemaskini Profil & Media Sosial
    Route::post('/profile', [StudioHubController::class, 'updateProfile'])->name('profile.update');

    // Ciri 3: Tetapan Akaun Bank
    Route::post('/bank', [StudioHubController::class, 'updateBank'])->name('bank.update');

    // Ciri 1 & 3: Tuntutan Payout
    Route::post('/wallet/payout', [StudioHubController::class, 'requestPayout'])->name('wallet.payout');

    // Ciri 5: Eksport Penyata Kewangan (CSV)
    Route::get('/wallet/export', [StudioHubController::class, 'exportTransactions'])->name('wallet.export');

    // Ciri 11: Penjanaan Kod Promosi
    Route::post('/promos', [StudioHubController::class, 'storePromoCode'])->name('promos.store');
    Route::delete('/promos/{promoCode}', [StudioHubController::class, 'deletePromoCode'])->name('promos.destroy');

    // Ciri 10: Pengurusan Komen Komuniti (Balas & Padam)
    Route::post('/comments/{comment}/reply', [StudioHubController::class, 'replyComment'])->name('comments.reply');
    Route::delete('/comments/{comment}', [StudioHubController::class, 'deleteComment'])->name('comments.destroy');

    // Ciri 14: Pengurusan Pasukan
    Route::post('/team', [StudioHubController::class, 'storeTeamMember'])->name('team.store');
    Route::delete('/team/{member}', [StudioHubController::class, 'deleteTeamMember'])->name('team.destroy');

    // Ciri 15: Tiket Sokongan & DMCA/Laporan Pelanggaran
    Route::post('/tickets', [StudioHubController::class, 'storeSupportTicket'])->name('tickets.store');

    // Data Extraction & Auto-Fill from YouTube
    Route::post('/youtube/extract', [CreatorEpisodeController::class, 'extractYouTube'])
        ->name('youtube.extract');

    // Feature 1: Drama Episode Dashboard
    Route::get('/dramas/{drama}', [CreatorEpisodeController::class, 'index'])
        ->name('dramas.manage');
    Route::get('/dramas/{drama}/episodes', [CreatorEpisodeController::class, 'index'])
        ->name('dramas.episodes.index');

    // Drama Main Series Details Edit & Update (14 Features)
    Route::get('/dramas/{drama}/edit', [\App\Http\Controllers\Creator\DramaController::class, 'edit'])
        ->name('dramas.edit');
    Route::match(['put', 'patch'], '/dramas/{drama}', [\App\Http\Controllers\Creator\DramaController::class, 'update'])
        ->name('dramas.update');

    // Feature 7: Drag-and-drop Reorder
    Route::post('/dramas/{drama}/episodes/reorder', [CreatorEpisodeController::class, 'reorder'])
        ->name('dramas.episodes.reorder');

    // Feature 5 & 6: Quick Togglings (Status & Paywall toggle)
    Route::post('/dramas/{drama}/episodes/{episode}/toggle-status', [CreatorEpisodeController::class, 'toggleStatus'])
        ->name('dramas.episodes.toggle-status');
    Route::post('/dramas/{drama}/episodes/{episode}/toggle-free', [CreatorEpisodeController::class, 'toggleFree'])
        ->name('dramas.episodes.toggle-free');

    // Form to create new episode
    Route::get('/dramas/{drama}/episodes/create', [CreatorEpisodeController::class, 'create'])
        ->name('dramas.episodes.create');

    // Store new episode
    Route::post('/dramas/{drama}/episodes', [CreatorEpisodeController::class, 'store'])
        ->name('dramas.episodes.store');

    // Feature 2 & 3: Form to edit episode (Metadata, Swap Media, Custom Thumbnail)
    Route::get('/dramas/{drama}/episodes/{episode}/edit', [CreatorEpisodeController::class, 'edit'])
        ->name('dramas.episodes.edit');

    // Update episode
    Route::match(['put', 'patch'], '/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'update'])
        ->name('dramas.episodes.update');

    // Delete episode
    Route::delete('/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'destroy'])
        ->name('dramas.episodes.destroy');
});

// Public Creator Profile Showcase & Fan Tipping (Ciri 6, 7, 9, 12)
Route::get('/creator/profile/{user}', [StudioHubController::class, 'publicProfile'])->name('creator.public-profile');
Route::post('/creator/profile/{user}/tip', [StudioHubController::class, 'submitFanTip'])->name('creator.tip.submit');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/demo/login/{user}', [AuthController::class, 'demoLogin'])->name('demo.login');
