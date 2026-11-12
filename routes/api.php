<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TvApiController;

/*
|--------------------------------------------------------------------------
| API Routes for Android TV & Smart TV Apps
|--------------------------------------------------------------------------
|
| Dedicated endpoints for Android TV Leanback client, Smart TV apps,
| and Chromecast / AirPlay companion devices.
|
*/

Route::prefix('tv')->group(function () {
    Route::get('/bootstrap', [TvApiController::class, 'bootstrap']);
    Route::get('/dramas', [TvApiController::class, 'dramas']);
    Route::get('/dramas/{slug}', [TvApiController::class, 'showDrama']);
    Route::get('/episodes/{episode}', [TvApiController::class, 'showEpisode']);
    Route::post('/playback/progress', [TvApiController::class, 'saveProgress']);
});

