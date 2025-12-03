<?php

namespace App\Providers;

use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs\ElevenlabsTranscriptionRepository;
use Canalizador\Video\Application\UseCases\DownloadVideo;
use Canalizador\Video\Application\UseCases\SaveAudio;
use Canalizador\Video\Application\UseCases\SaveTranscription;
use Canalizador\Video\Application\UseCases\GetYoutubeVideo;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
use Canalizador\Video\Infrastructure\Tools\AudioExtractor;
use Canalizador\Video\Infrastructure\Tools\AudioTranscription;
use Canalizador\Video\Infrastructure\Tools\VideoDownloader;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(YoutubeDataApiClient::class, function ($app) {
            $apiKey = config('services.youtube.api_key');

            return new YoutubeDataApiClient($apiKey);
        });

        $this->app->bind(
            VideoRepository::class,
            RedisVideoRepository::class
        );

        $this->app->bind(
            VideoRepository::class,
            YoutubeVideoRepository::class
        );

        $this->app->bind(GetYoutubeVideo::class, function ($app) {
            return new GetYoutubeVideo(
                externalVideoRepository: $app->make(YoutubeVideoRepository::class)
            );
        });

        $this->app->bind(SaveTranscription::class, function ($app) {
            return new SaveTranscription(
                videoRepository: $app->make(RedisVideoRepository::class),
                transcriptionRepository: $app->make(ElevenlabsTranscriptionRepository::class)
            );
        });

        $this->app->bind(SaveAudio::class, function ($app) {
            return new SaveAudio(
                videoRepository: $app->make(RedisVideoRepository::class),
            );
        });

        $this->app->bind(DownloadVideo::class, function ($app) {
            return new DownloadVideo(
                videoRepository: $app->make(RedisVideoRepository::class),
                externalVideoRepository: $app->make(YoutubeVideoRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
