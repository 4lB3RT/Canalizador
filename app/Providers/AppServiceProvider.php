<?php

namespace App\Providers;

use Canalizador\Script\Application\UseCases\GenerateScript;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Video\Application\UseCases\GetYoutubeVideo;
use Canalizador\Video\Application\UseCases\SaveTranscription;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
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

        $this->app->bind(GenerateScript::class, function ($app) {
            return new GenerateScript(
                scriptRepository: $app->make(EloquentScriptRepository::class),
                scriptGenerator: $app->make(OpenAIScriptGenerator::class)
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
