<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Drama;
use App\Models\Episode;
use App\Http\Controllers\EpisodeWatchController;
use App\Http\Controllers\Creator\EpisodeController as CreatorEpisodeController;
use App\Http\Controllers\Creator\DramaController as CreatorDramaController;
use App\Http\Controllers\Creator\StudioHubController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes - Filemai.my Platform
|--------------------------------------------------------------------------
|
| Main vertical mini-drama browsing, video player streaming, creator studio
| management, VIP subscription, and user authentication routes.
|
*/

// Home / Discovery Feed (Netflix Style Grid)
Route::get('/', function (Request $request) {
    $selectedGenre = $request->query('genre', 'all');
    $activeTab = $request->query('tab', 'discover');
    $search = $request->query('q');

    $query = Drama::query()->where('status', 'published');

    if ($selectedGenre && $selectedGenre !== 'all') {
        $query->where('genre', $selectedGenre);
    }

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('synopsis', 'like', "%{$search}%");
        });
    }

    if ($activeTab === 'trends') {
        $query->orderBy('views_count', 'desc');
    } elseif ($activeTab === 'popular') {
        $query->where('featured', true)->orderBy('views_count', 'desc');
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $dramas = $query->with('episodes')->paginate(12);

    $heroDramas = Drama::where('featured', true)->where('status', 'published')->take(5)->get();
    if ($heroDramas->isEmpty()) {
        $heroDramas = Drama::where('status', 'published')->take(5)->get();
    }

    $trendsNow = Drama::where('status', 'published')->orderBy('views_count', 'desc')->take(6)->get();
    $popular = Drama::where('status', 'published')->inRandomOrder()->take(6)->get();
    $premieres = Drama::where('status', 'published')->orderBy('created_at', 'desc')->take(6)->get();

    $genres = ['Romance', 'Action', 'Thriller', 'CEO', 'Revenge', 'Drama', 'Komedi', 'Fantasi'];

    return view('welcome', compact('dramas', 'heroDramas', 'trendsNow', 'popular', 'premieres', 'genres', 'selectedGenre', 'activeTab', 'search'));
})->name('home');

// Drama Landing & Main Details Page (Showcasing 14 Features)
Route::get('/drama/{drama:slug}', function (Drama $drama) {
    $episodes = $drama->episodes()
        ->where(function ($q) {
            $q->where('status', 'published')->orWhereNull('status');
        })
        ->orderBy('episode_number', 'asc')
        ->get();

    return view('dramas.show', [
        'drama' => $drama,
        'episodes' => $episodes,
    ]);
})->name('drama.show');

// Episode Player (Vertical Fullscreen View)
Route::get('/drama/{drama:slug}/episode/{episode}', [EpisodeWatchController::class, 'show'])
    ->name('episodes.show');

// Friendly Direct Episode Watch URL: /watch/{episode}
Route::get('/watch/{episode}', function ($episode) {
    if (!($episode instanceof Episode)) {
        $ep = is_numeric($episode) ? Episode::find($episode) : null;
        if (!$ep) {
            $ep = Episode::where('episode_number', $episode)->first();
        }
        if (!$ep) {
            abort(404, 'Episod tidak ditemui.');
        }
        $episode = $ep;
    }
    $drama = $episode->drama;
    if (!$drama) {
        abort(404, 'Drama untuk episod ini tidak ditemui.');
    }
    return redirect()->route('episodes.show', [
        'drama' => $drama->slug,
        'episode' => $episode->episode_number,
    ]);
})->name('watch.direct');

// Admin Analytics & Settings (Protected by auth and admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

    // Admin Settings: Pricing for Viewer Subscription, Creator Package & Watch Earnings per Block
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])
        ->name('settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])
        ->name('settings.update');
    Route::post('/settings/payout/{payout}', [\App\Http\Controllers\Admin\SettingController::class, 'updatePayout'])
        ->name('settings.payout.update');
});

// Creator Studio Episode & Drama Management (Protected by auth)
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
    Route::post('/comments/reply', [StudioHubController::class, 'replyComment'])->name('comments.reply');
    Route::delete('/comments/{comment}', [StudioHubController::class, 'deleteComment'])->name('comments.destroy');

    // Ciri 13: Sistem Tiket Sokongan & Aduan Pelanggaran Hak Cipta
    Route::post('/tickets', [StudioHubController::class, 'storeSupportTicket'])->name('tickets.store');

    // Ciri 15: Pengurusan Pasukan & Akses Kolaborasi
    Route::post('/team', [StudioHubController::class, 'addTeamMember'])->name('team.store');
    Route::delete('/team/{teamMember}', [StudioHubController::class, 'deleteTeamMember'])->name('team.destroy');

    // Data Extraction & Auto-Fill from YouTube Endpoint
    Route::post('/youtube/extract', [CreatorEpisodeController::class, 'extractYouTube'])->name('youtube.extract');

    // Drama Creation Wizard
    Route::get('/dramas/create', [CreatorDramaController::class, 'create'])->name('dramas.create');
    Route::post('/dramas', [CreatorDramaController::class, 'store'])->name('dramas.store');

    // Manage Drama Series
    Route::get('/dramas/{drama}/manage', [CreatorDramaController::class, 'manage'])->name('dramas.manage');
    Route::post('/dramas/{drama}/destroy', [CreatorDramaController::class, 'destroy'])->name('dramas.destroy');

    // Wallet Overview
    Route::get('/wallet', [\App\Http\Controllers\Creator\WalletController::class, 'index'])->name('wallet');

    // Feature 1: Episode Dashboard (Senarai Episod)
    Route::get('/dramas/{drama}/episodes', [CreatorEpisodeController::class, 'index'])
        ->name('dramas.episodes.index');

    // Edit Drama Details (14 Features + 24-Hour Rule)
    Route::get('/dramas/{drama}/edit', [CreatorDramaController::class, 'edit'])
        ->name('dramas.edit');
    Route::match(['put', 'patch', 'post'], '/dramas/{drama}', [CreatorDramaController::class, 'update'])
        ->name('dramas.update');

    // Feature 7: Drag-and-drop Reorder
    Route::post('/dramas/{drama}/episodes/reorder', [CreatorEpisodeController::class, 'reorder'])
        ->name('dramas.episodes.reorder');

    // Feature 5 & 6: Quick Togglings (Status & Paywall toggle)
    Route::post('/dramas/{drama}/episodes/{episode}/toggle-status', [CreatorEpisodeController::class, 'toggleStatus'])
        ->name('dramas.episodes.toggle-status');
    Route::post('/dramas/{drama}/episodes/{episode}/toggle-free', [CreatorEpisodeController::class, 'toggleFree'])
        ->name('dramas.episodes.toggle-free');

    // Friendly alias for singular drama URL
    Route::get('/drama/{drama}', function (Drama $drama) {
        return redirect()->route('creator.dramas.manage', $drama);
    });

    // Form to create new episode
    Route::get('/dramas/{drama}/episodes/create', [CreatorEpisodeController::class, 'create'])
        ->name('dramas.episodes.create');

    // Store new episode
    Route::post('/dramas/{drama}/episodes', [CreatorEpisodeController::class, 'store'])
        ->name('dramas.episodes.store');

    // Form to edit episode
    Route::get('/dramas/{drama}/episodes/{episode}/edit', [CreatorEpisodeController::class, 'edit'])
        ->name('dramas.episodes.edit');

    // Update episode
    Route::match(['put', 'patch', 'post'], '/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'update'])
        ->name('dramas.episodes.update');

    // Delete episode
    Route::delete('/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'destroy'])
        ->name('dramas.episodes.destroy');
});

// Public Creator Profile Showcase & Fan Tipping (Ciri 6, 7, 9, 12)
Route::get('/creator/profile/{user}', [StudioHubController::class, 'publicProfile'])->name('creator.public-profile');
Route::post('/creator/profile/{user}/tip', [StudioHubController::class, 'submitFanTip'])->name('creator.tip.submit');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Register, Login & Logout)
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

// Demo Account Switchers (Staging / Dev)
Route::get('/demo/login/{user}', function (App\Models\User $user) {
    \Illuminate\Support\Facades\Auth::login($user);
    request()->session()->regenerate();
    if (in_array($user->role, ['creator', 'admin'])) {
        return redirect()->route('creator.dashboard')->with('success', "Log masuk sebagai {$user->name} ({$user->role})");
    }
    return redirect()->route('home')->with('success', "Log masuk sebagai {$user->name}");
})->name('demo.login');

Route::get('/demo/switch/{id}', [AuthController::class, 'demoSwitch'])->name('demo.switch');

// VIP Subscription Info Page
Route::get('/subscribe', function () {
    $monthlyPrice = \App\Models\SystemSetting::get('viewer_vip_monthly_price', 14.90);
    $yearlyPrice = \App\Models\SystemSetting::get('viewer_vip_yearly_price', 129.90);
    $creatorPackage = \App\Models\SystemSetting::get('creator_package_price', 50.00);
    return view('subscribe', [
        'title' => "Langgan Pas VIP Filemai - RM " . number_format($monthlyPrice, 2) . " / Bulan",
        'monthlyPrice' => $monthlyPrice,
        'yearlyPrice' => $yearlyPrice,
        'creatorPackage' => $creatorPackage,
    ]);
})->name('subscribe');

// Real-time Watch Royalty & Analytics Endpoint (for player continuous playback tracking)
Route::post('/api/episodes/{episode}/track-watch', function (Episode $episode, Request $request) {
    $duration = (int) $request->input('duration', 60);
    $drama = $episode->drama;
    if (!$drama) {
        return response()->json(['credited' => false, 'error' => 'Drama not found'], 404);
    }
    $result = \App\Models\CreatorWallet::recordViewEarnings($drama, $episode, $duration);
    return response()->json($result);
});

// Dedicated Android TV & Smart TV Leanback Routes
Route::get('/tv', [\App\Http\Controllers\TvController::class, 'index'])->name('tv.index');
Route::get('/tv/watch/{slug}/{episode?}', [\App\Http\Controllers\TvController::class, 'watch'])->name('tv.watch');

// Web-session route for TV Playback Progress
Route::post('/api/tv/playback/progress', [\App\Http\Controllers\Api\TvApiController::class, 'saveProgress']);
