<?php

namespace App\Providers;

use Canalizador\Transcription\Domain\Repositories\TranscriptionRepository;
use Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs\ElevenlabsTranscriptionRepository;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Agents\AudioTranscriptor;
use Canalizador\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
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
        $this->app->bind(
            VideoRepository::class,
            RedisVideoRepository::class
        );

        $this->app->bind(VideoDownloader::class, function ($app) {
            return new VideoDownloader($app->make(RedisVideoRepository::class));
        });

        $this->app->bind(AudioExtractor::class, function ($app) {
            return new AudioExtractor($app->make(RedisVideoRepository::class));
        });

        $this->app->bind(AudioTranscription::class, function ($app) {
            return new AudioTranscription(
                $app->make(RedisVideoRepository::class),
                $app->make(ElevenlabsTranscriptionRepository::class)
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
