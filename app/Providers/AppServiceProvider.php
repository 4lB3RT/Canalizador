<?php

namespace App\Providers;

use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Script\Infrastructure\Repositories\Eloquent\EloquentScriptRepository;
use Canalizador\Script\Infrastructure\Repositories\OpenAI\OpenAIScriptGenerator;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs\ElevenlabsTranscriptionRepository;
use Canalizador\Video\Application\UseCases\GenerateVideo\GenerateVideo;
use Canalizador\Video\Application\UseCases\RetrieveVideoContent\RetrieveVideoContent;
use Canalizador\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\FFmpeg\FFmpegVideoComposer;
use Canalizador\Video\Infrastructure\Repositories\Luma\LumaVideoGenerator;
use Canalizador\Video\Infrastructure\Repositories\OpenAI\OpenAITextToSpeechGenerator;
use Canalizador\Video\Infrastructure\Repositories\Sora\SoraVideoRepository;
use Canalizador\VideoLegacy\Application\UseCases\GetYoutubeVideo;
use Canalizador\VideoLegacy\Domain\Repositories\VideoRepository as VideoLegacyRepository;
use Canalizador\VideoLegacy\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\VideoLegacy\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
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
            VideoLegacyRepository::class,
            RedisVideoRepository::class
        );

        $this->app->bind(
            VideoLegacyRepository::class,
            YoutubeVideoRepository::class
        );

        $this->app->bind(GetYoutubeVideo::class, function ($app) {
            return new GetYoutubeVideo(
                externalVideoRepository: $app->make(YoutubeVideoRepository::class)
            );
        });

        $this->app->bind(GenerateScript::class, function ($app) {
            return new GenerateScript(
                scriptRepository: $app->make(EloquentScriptRepository::class),
                scriptGenerator: $app->make(OpenAIScriptGenerator::class)
            );
        });

        $this->app->bind(ScriptRepository::class, EloquentScriptRepository::class);

        $this->app->bind(VideoRepository::class, function ($app) {
            return new EloquentVideoRepository(
                scriptRepository: $app->make(ScriptRepository::class)
            );
        });

        $this->app->bind(VideoGenerator::class, function ($app) {
            $provider = env('VIDEO_GENERATOR_PROVIDER', 'luma');

            return match ($provider) {
                'sora' => new SoraVideoRepository(
                    apiKey: config('services.openai.key') ?? ''
                ),
                default => new LumaVideoGenerator(
                    apiKey: config('services.luma.api_key') ?? ''
                ),
            };
        });

        $this->app->bind(GenerateVideo::class, function ($app) {
            return new GenerateVideo(
                generateScript: $app->make(GenerateScript::class),
                videoRepository: $app->make(VideoRepository::class),
                videoGenerator: $app->make(VideoGenerator::class),
            );
        });

        $this->app->bind(VideoContentRetriever::class, function ($app) {
            $provider = env('VIDEO_GENERATOR_PROVIDER', 'luma');

            return match ($provider) {
                'sora' => new SoraVideoRepository(
                    apiKey: config('services.openai.key') ?? ''
                ),
                default => new SoraVideoRepository(
                    apiKey: config('services.openai.key') ?? ''
                ),
            };
        });

        $this->app->bind(RetrieveVideoContent::class, function ($app) {
            return new RetrieveVideoContent(
                videoContentRetriever: $app->make(VideoContentRetriever::class),
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
