<?php

use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs\ElevenlabsTranscriptionRepository;
use Canalizador\Video\Application\UseCases\DownloadVideo;
use Canalizador\Video\Application\UseCases\SaveAudio;
use Canalizador\Video\Application\UseCases\SaveTranscription;
use Canalizador\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
use Canalizador\Video\Infrastructure\Tools\AudioExtractor;
use Canalizador\Video\Infrastructure\Tools\AudioTranscription;
use Canalizador\Video\Infrastructure\Tools\VideoDownloader;
use Illuminate\Support\Facades\Route;

Route::get('/tools/debug', function () {
    $videoRepository = new RedisVideoRepository(
        \Illuminate\Support\Facades\Redis::connection()
    );

    $externalVideoRepository = new YoutubeVideoRepository(
        new YoutubeDataApiClient(env('YOUTUBE_API_KEY')),
    );

    $videoDownlader = new VideoDownloader(
        new DownloadVideo(
            videoRepository: $videoRepository,
            externalVideoRepository: $externalVideoRepository
        )
    );

    $audioExtractor = new AudioExtractor(
        saveAudio: new SaveAudio(
            videoRepository: $videoRepository,
        )
    );

    $audioTranscription = new AudioTranscription(
        new SaveTranscription(
            videoRepository: $videoRepository,
            transcriptionRepository: new ElevenlabsTranscriptionRepository()
        )
    );

    $videoDownlader('2V2M-la_4RI');
    $audioExtractor('2V2M-la_4RI');
    $audioTranscription('2V2M-la_4RI');
})->name('tools.debug');
