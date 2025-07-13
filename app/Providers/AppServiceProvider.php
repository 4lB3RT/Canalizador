<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Repositories\YoutubeVideoRepository;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VideoRepository::class, YoutubeVideoRepository::class);
        $this->app->bind(YoutubeDataApiClient::class, function ($app) {
            $apiKey = config('services.youtube.api_key');
            return new YoutubeDataApiClient($apiKey);
        });

        $this->app->bind(YoutubeAnalyticsApiClient::class, function ($app) {
            $config = config('services.youtube_analytics');
            $accessToken = null; // You may want to load this from storage/session
            return new YoutubeAnalyticsApiClient(
                $config['client_id'],
                $config['client_secret'],
                $config['redirect_uri'],
                $accessToken
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
