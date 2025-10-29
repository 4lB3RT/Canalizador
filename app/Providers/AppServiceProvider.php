<?php

namespace App\Providers;

use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
use Canalizador\Video\Infrastructure\Tools\AudioExtractor;
use Canalizador\Video\Infrastructure\Tools\VideoDownloader;
use Illuminate\Support\ServiceProvider;
use OpenAI;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VideoRepository::class, YoutubeVideoRepository::class);

        $this->app->bind(VideoDownloader::class, function ($app) {
            return new VideoDownloader($app->make(RedisVideoRepository::class));
        });

        $this->app->bind(AudioExtractor::class, function ($app) {
            return new AudioExtractor($app->make(RedisVideoRepository::class));
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
