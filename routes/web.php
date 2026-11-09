<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Drama;
use App\Models\Episode;
use App\Http\Controllers\EpisodeWatchController;
use App\Http\Controllers\Creator\EpisodeController as CreatorEpisodeController;
use App\Http\Controllers\Creator\DramaController as CreatorDramaController;
use App\Http\Controllers\Auth\AuthController;

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

// Drama Landing Redirect to Episode 1
Route::get('/drama/{drama:slug}', function (Drama $drama) {
    $firstEpisode = $drama->episodes()->orderBy('episode_number', 'asc')->first();
    $epNum = $firstEpisode ? $firstEpisode->episode_number : 1;
    return redirect()->route('episodes.show', [
        'drama' => $drama->slug,
        'episode' => $epNum,
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

// Creator Studio Episode & Drama Management (Protected by auth)
Route::middleware(['auth'])->prefix('creator')->name('creator.')->group(function () {
    
    // Creator Studio Dashboard
    Route::get('/', [CreatorDramaController::class, 'dashboard'])
        ->name('dashboard');

    // Create New Drama Form (Placed before {drama} parameter route)
    Route::get('/dramas/create', [CreatorDramaController::class, 'create'])
        ->name('dramas.create');

    // Store New Drama (with optional upfront episode list)
    Route::post('/dramas', [CreatorDramaController::class, 'store'])
        ->name('dramas.store');

    // Friendly aliases for drama creation
    Route::get('/drama/new', function () {
        return redirect()->route('creator.dramas.create');
    });
    Route::get('/drama/create', function () {
        return redirect()->route('creator.dramas.create');
    });

    // Manage Drama & Episodes List (creator.dramas.manage)
    Route::get('/dramas/{drama}', [CreatorDramaController::class, 'manage'])
        ->name('dramas.manage');

    // Friendly alias for singular drama URL
    Route::get('/drama/{drama}', function (Drama $drama) {
        return redirect()->route('creator.dramas.manage', $drama);
    });

    // Form to create YouTube / Storage / Coming Soon episode
    Route::get('/dramas/{drama}/episodes/create', [CreatorEpisodeController::class, 'create'])
        ->name('dramas.episodes.create');

    // Store episode
    Route::post('/dramas/{drama}/episodes', [CreatorEpisodeController::class, 'store'])
        ->name('dramas.episodes.store');

    // Edit episode form
    Route::get('/dramas/{drama}/episodes/{episode}/edit', [CreatorEpisodeController::class, 'edit'])
        ->name('dramas.episodes.edit');

    // Update episode
    Route::put('/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'update'])
        ->name('dramas.episodes.update');
    Route::post('/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'update']);

    // Delete episode
    Route::delete('/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'destroy'])
        ->name('dramas.episodes.destroy');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Real Register, Login & Logout)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout']); // Fallback GET
});

// Quick 1-click Demo Account Switcher (Staging/Dev)
Route::get('/demo/switch/{id}', [AuthController::class, 'demoSwitch'])->name('demo.switch');

// VIP Subscription Info Page
Route::get('/subscribe', function () {
    return view('subscribe', [
        'title' => 'Langgan Pas VIP Filemai - RM5 / Bulan',
    ]);
})->name('subscribe');

