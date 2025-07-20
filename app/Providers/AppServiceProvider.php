<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Repositories\YoutubeVideoRepository;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;

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

        $this->app->bind(VideoRepository::class, YoutubeVideoRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
