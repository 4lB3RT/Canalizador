<?php

declare(strict_types = 1);

use Canalizador\Video\Infrastructure\Http\Web\Controllers\LiveChatController;
use Canalizador\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\Video\Infrastructure\Tools\VideoDownloader;
use Illuminate\Support\Facades\Route;

Route::get('/tools/debug', function () {
    $videoRepository = new RedisVideoRepository(
        \Illuminate\Support\Facades\Redis::connection()
    );

    $videoDownloader = new VideoDownloader(
        $videoRepository
    );

    $audioExtractor = new \Canalizador\Video\Infrastructure\Tools\AudioExtractor(
        $videoRepository
    );

    $audioTranscription = new \Canalizador\Video\Infrastructure\Tools\AudioTranscription(
        $videoRepository,
        new \Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs\ElevenlabsTranscriptionRepository(
            $videoRepository
        )
    );

    $videoDownloader('2V2M-la_4RI');
    $audioExtractor('2V2M-la_4RI');
    $audioTranscription('2V2M-la_4RI');
})->name('tools.debug');
