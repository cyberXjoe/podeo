<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EpisodeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('login', 'AuthController@login');


Route::middleware('jwt.auth')->group(function () {
    // Add episode
    Route::post('episodes', 'EpisodeController@addEpisode');

    // Get signed URL for private episode
    Route::get('episodes/{id}/signed-url', 'EpisodeController@getSignedUrl');

    // Route::get('/episode/{id}', 'EpisodeController@getEpisode')->name('getEpisode');
});


Route::middleware(['auth:api','admin'])->group(function () {
    Route::post('add-episode', [EpisodeController::class, 'addEpisode']);
    Route::put('episodes/{id}/flag-private', [EpisodeController::class, 'flagAsPrivate']);
    Route::get('get-signed-url/{id}', [EpisodeController::class, 'getSignedUrl']);
    Route::get('stream-episode/{id}', [EpisodeController::class, 'streamEpisode']);
});

// Media Streaming Microservice routes
Route::prefix('episode')->group(function () {
    Route::get('/{id}', 'EpisodeController@streamEpisode')->name('stream.episode');
    Route::get('/signed/{id}', 'EpisodeController@getSignedEpisodeUrl')->name('signed.episode');
});

// Analytics Service routes
Route::prefix('analytics')->group(function () {
    Route::post('/log', 'AnalyticsController@logEpisodeRequest')->name('analytics.log');
});
