<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EpisodeWatchController;
use App\Http\Controllers\Creator\EpisodeController as CreatorEpisodeController;

/*
|--------------------------------------------------------------------------
| Web Routes - Filemai.my Vertical Streaming Platform
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Viewer Watch Route (Ad-Gated 9:16 Vertical Player)
Route::get('/drama/{drama:slug}/episode/{episode:episode_number}', [EpisodeWatchController::class, 'show'])
    ->name('episodes.show');

// Alternate direct episode route
Route::get('/watch/{episode}', function (\App\Models\Episode $episode) {
    return redirect()->route('episodes.show', [
        'drama' => $episode->drama->slug,
        'episode' => $episode->episode_number,
    ]);
})->name('watch.direct');

// Creator Studio Episode Management (Protected by auth & creator middleware)
Route::middleware(['auth'])->prefix('creator')->name('creator.')->group(function () {
    
    // Form to create YouTube episode
    Route::get('/dramas/{drama}/episodes/create', [CreatorEpisodeController::class, 'create'])
        ->name('dramas.episodes.create');

    // Store YouTube episode
    Route::post('/dramas/{drama}/episodes', [CreatorEpisodeController::class, 'store'])
        ->name('dramas.episodes.store');

    // Delete episode
    Route::delete('/dramas/{drama}/episodes/{episode}', [CreatorEpisodeController::class, 'destroy'])
        ->name('dramas.episodes.destroy');
});
