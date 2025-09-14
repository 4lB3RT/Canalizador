<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Repositories\YoutubeVideoRepository;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use OpenAI;

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

        $this->app->bind(OpenAI::class, function ($app) {
            $httpClient = new \GuzzleHttp\Client([]);
            return OpenAI::factory()
                ->withApiKey(config('services.openai.api_key'))
                ->withOrganization(config('services.openai.organization'))
                ->withProject(config('services.openai.project'))
                ->withBaseUri(config('services.openai.base_uri', 'https://api.openai.com/v1'))
                ->withHttpClient($httpClient)
                ->withHttpHeader('X-My-Header', 'foo')
                ->withQueryParam('my-param', 'bar')
                ->withStreamHandler(fn ($request) => $httpClient->send($request, ['stream' => true]))
                ->make();
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
