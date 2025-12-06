<?php

use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs\ElevenlabsTranscriptionRepository;
use Canalizador\VideoLegacy\Application\UseCases\DownloadVideo;
use Canalizador\VideoLegacy\Application\UseCases\SaveAudio;
use Canalizador\VideoLegacy\Application\UseCases\SaveTranscription;
use Canalizador\VideoLegacy\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\VideoLegacy\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
use Canalizador\VideoLegacy\Infrastructure\Tools\AudioExtractor;
use Canalizador\VideoLegacy\Infrastructure\Tools\AudioTranscription;
use Canalizador\VideoLegacy\Infrastructure\Tools\VideoDownloader;
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
