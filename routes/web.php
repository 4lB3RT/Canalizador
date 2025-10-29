<?php

declare(strict_types = 1);

use Canalizador\Video\Infrastructure\Http\Web\Controllers\LiveChatController;
use Canalizador\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\Video\Infrastructure\Tools\VideoDownloader;
use Illuminate\Support\Facades\Route;

Route::middleware(['google.token'])->group(function () {
    Route::get('/livechat', [LiveChatController::class, 'index']);
    Route::post('/livechat/send', [LiveChatController::class, 'send']);
    Route::get('/livechat/stream', [LiveChatController::class, 'stream']);

    Route::get('/auth/google/callback')->name('google.callback');
});

Route::get('/tools/debug', function () {
    $videoDownloader = new VideoDownloader(
        new RedisVideoRepository(
            \Illuminate\Support\Facades\Redis::connection()
        )
    );

    $audioExtractor = new \Canalizador\Video\Infrastructure\Tools\AudioExtractor(
        new RedisVideoRepository(
            \Illuminate\Support\Facades\Redis::connection()
        )
    );

    $videoDownloader('2V2M-la_4RI');
    $audioExtractor('2V2M-la_4RI');

})->name('tools.debug');

