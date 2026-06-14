<?php

declare(strict_types=1);

namespace App\Providers;

use Helmreel\VideoProduction\Avatar\Application\UseCases\CreateAvatar\CreateAvatar;
use Helmreel\VideoProduction\Avatar\Application\UseCases\DeleteAvatar\DeleteAvatar;
use Helmreel\VideoProduction\Avatar\Application\UseCases\GenerateAvatarMetadata\GenerateAvatarMetadata;
use Helmreel\VideoProduction\Avatar\Application\UseCases\GetAvatar\GetAvatar;
use Helmreel\VideoProduction\Avatar\Application\UseCases\GetAvatars\GetAvatars;
use Helmreel\VideoProduction\Avatar\Domain\Factories\AvatarFactory;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Mappers\CreateAvatarRequestMapper;
use Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\Eloquent\EloquentAvatarRepository;
use Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI\OpenAiAvatarRepository;
use Helmreel\VideoProduction\Clip\Application\Handlers\OnAllClipsCompletedHandler;
use Helmreel\VideoProduction\Clip\Application\Handlers\OnClipCompletedHandler;
use Helmreel\VideoProduction\Clip\Application\Handlers\OnClipCreatedHandler;
use Helmreel\VideoProduction\Clip\Application\Handlers\OnClipGeneratedHandler;
use Helmreel\VideoProduction\Clip\Application\Handlers\OnVideoCreatedHandler;
use Helmreel\VideoProduction\Clip\Application\UseCases\ComposeShort\ComposeShort;
use Helmreel\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClip;
use Helmreel\VideoProduction\Clip\Application\UseCases\DownloadClip\DownloadClip;
use Helmreel\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClip;
use Helmreel\VideoProduction\Clip\Domain\Events\AllClipsCompleted;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipCompleted;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipCreated;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipGenerated;
use Helmreel\VideoProduction\Clip\Domain\Factories\ClipFactory;
use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipDownloader;
use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Helmreel\VideoProduction\Clip\Domain\Services\VideoComposer;
use Helmreel\VideoProduction\Clip\Infrastructure\Repositories\Eloquent\EloquentClipRepository;
use Helmreel\VideoProduction\Clip\Infrastructure\Repositories\Veo\VeoClipDownloader;
use Helmreel\VideoProduction\Clip\Infrastructure\Services\FfmpegVideoComposer;
use Helmreel\VideoProduction\Image\Application\UseCases\GetImageFile\GetImageFile;
use Helmreel\VideoProduction\Image\Domain\Factories\ImageFactory;
use Helmreel\VideoProduction\Image\Domain\Repositories\ImageRepository;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\Shared\Media\Infrastructure\Repositories\Eloquent\EloquentMediaRepository;
use Helmreel\VideoProduction\Image\Infrastructure\Repositories\Eloquent\EloquentImageRepository;
use Helmreel\VideoProduction\News\Application\UseCases\DownloadNews\DownloadNews;
use Helmreel\VideoProduction\News\Domain\Repositories\NewsProvider;
use Helmreel\VideoProduction\News\Domain\Repositories\NewsRepository;
use Helmreel\VideoProduction\News\Infrastructure\Repositories\Eloquent\EloquentNewsRepository;
use Helmreel\VideoProduction\News\Infrastructure\Repositories\TresDJuegos\TresDJuegosClient;
use Helmreel\VideoProduction\Script\Domain\Factories\ScriptFactory;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptGenerator;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\Services\GenerateScript;
use Helmreel\VideoProduction\Script\Infrastructure\Repositories\Eloquent\EloquentScriptRepository;
use Helmreel\VideoProduction\Script\Infrastructure\Repositories\OpenAI\OpenAIScriptGenerator;
use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\Services\HttpClient;
use Helmreel\Shared\Shared\Domain\Services\HttpResponseValidator;
use Helmreel\Shared\Shared\Infrastructure\Console\SetupRabbitMQCommand;
use Helmreel\Shared\Shared\Infrastructure\Events\EventHandlerRegistry;
use Helmreel\VideoProduction\Video\Application\UseCases\ApplyVoice\ApplyVoice;
use Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo\CreateVideo;
use Helmreel\VideoProduction\Video\Application\UseCases\RetrieveVideoContent\RetrieveVideoContent;
use Helmreel\VideoProduction\Video\Domain\Events\VideoCreated;
use Helmreel\VideoProduction\Video\Domain\Factories\VideoFactory;
use Helmreel\VideoProduction\Weather\Domain\Repositories\ForecastRepository;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoContentRetriever;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoExtender;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoGenerator;
use Helmreel\Shared\Video\Domain\Repositories\VideoMetadataGenerator;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\Services\AvatarContextFrameGenerator;
use Helmreel\VideoProduction\Video\Domain\Services\FileSystem;
use Helmreel\VideoProduction\Video\Domain\Services\VideoFileValidator;
use Helmreel\VideoProduction\Video\Domain\Services\ScriptTranslator;
use Helmreel\VideoProduction\Video\Domain\Services\VideoPromptExtractor;
use Helmreel\VideoProduction\Video\Domain\Services\YouTubeServiceFactory;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Mappers\CreateVideoRequestMapper;
use Helmreel\VideoProduction\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Helmreel\Shared\Video\Infrastructure\Repositories\OpenAI\OpenAIVideoMetadataGenerator;
use Helmreel\VideoProduction\Video\Infrastructure\Repositories\Veo\VeoVideoRepository;
use Helmreel\VideoProduction\Video\Infrastructure\Services\JsonVideoPromptExtractor;
use Helmreel\VideoProduction\Video\Infrastructure\Services\OpenAiAvatarContextFrameGenerator;
use Helmreel\VideoProduction\Video\Infrastructure\Services\OpenAIScriptTranslator;
use Helmreel\VideoProduction\Video\Infrastructure\Services\LaravelFileSystem;
use Helmreel\VideoProduction\Video\Infrastructure\Services\VideoFileValidator as VideoFileValidatorImpl;
use Helmreel\VideoProduction\Video\Infrastructure\Services\YouTube\GoogleYouTubeServiceFactory;
use Helmreel\VideoProduction\Voice\Application\UseCases\CloneVoice\CloneVoice;
use Helmreel\VideoProduction\Voice\Domain\Repositories\AudioIsolator;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceCloner;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceGenerator;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsAudioIsolator;
use Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsVoiceCloner;
use Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsTextToSpeech;
use Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsVoiceGenerator;
use Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsVoiceRepository;
use Helmreel\VideoProduction\Weather\Application\UseCases\GetForecasts\GetForecasts;
use Helmreel\VideoProduction\Weather\Domain\Repositories\ForecastSummarizer;
use Helmreel\VideoProduction\Weather\Domain\Repositories\WeatherProvider;
use Helmreel\VideoProduction\Weather\Infrastructure\Repositories\Aemet\AemetWeatherProvider;
use Helmreel\VideoProduction\Weather\Infrastructure\Repositories\Eloquent\EloquentForecastRepository;
use Helmreel\VideoProduction\Weather\Infrastructure\Repositories\OpenAI\OpenAIForecastSummarizer;
use Helmreel\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use Illuminate\Support\ServiceProvider;

class VideoProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerScriptServices();
        $this->registerVideoServices();
        $this->registerClipServices();
        $this->registerAvatarServices();
        $this->registerImageServices();
        $this->registerVoiceServices();
        $this->registerWeatherServices();
        $this->registerNewsServices();
    }

    public function boot(): void
    {
        $this->commands([
            SetupRabbitMQCommand::class,
        ]);
    }

    private function registerScriptServices(): void
    {
        $this->app->bind(ScriptFactory::class, ScriptFactory::class);
        $this->app->bind(ScriptRepository::class, EloquentScriptRepository::class);
        $this->app->bind(ScriptGenerator::class, OpenAIScriptGenerator::class);

        $this->app->bind(GenerateScript::class, function ($app) {
            return new GenerateScript(
                scriptRepository: $app->make(EloquentScriptRepository::class),
                scriptGenerator: $app->make(ScriptGenerator::class),
                scriptFactory: $app->make(ScriptFactory::class),
            );
        });
    }

    private function registerVideoServices(): void
    {
        $this->app->bind(CreateVideoRequestMapper::class, CreateVideoRequestMapper::class);
        $this->app->bind(VideoPromptExtractor::class, JsonVideoPromptExtractor::class);
        $this->app->bind(ScriptTranslator::class, OpenAIScriptTranslator::class);
        $this->app->bind(VideoMetadataGenerator::class, OpenAIVideoMetadataGenerator::class);
        $this->app->bind(FileSystem::class, LaravelFileSystem::class);
        $this->app->bind(YouTubeServiceFactory::class, GoogleYouTubeServiceFactory::class);

        $this->app->bind(VideoRepository::class, function ($app) {
            return new EloquentVideoRepository(
                scriptRepository: $app->make(ScriptRepository::class)
            );
        });

        $this->app->singleton(VeoVideoRepository::class, function ($app) {
            return new VeoVideoRepository(
                apiKey: config('services.google.veo_api_key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class)
            );
        });

        $this->app->bind(VideoGenerator::class, VeoVideoRepository::class);
        $this->app->bind(VideoContentRetriever::class, VeoVideoRepository::class);
        $this->app->bind(VideoExtender::class, VeoVideoRepository::class);

        $this->app->bind(VideoFactory::class, function ($app) {
            return new VideoFactory(
                clock: $app->make(Clock::class)
            );
        });

        $this->app->bind(CreateVideo::class, function ($app) {
            return new CreateVideo(
                scriptRepository: $app->make(ScriptRepository::class),
                generateScript: $app->make(GenerateScript::class),
                videoFactory: $app->make(VideoFactory::class),
                videoRepository: $app->make(VideoRepository::class),
                videoMetadataGenerator: $app->make(VideoMetadataGenerator::class),
                eventBus: $app->make(EventBus::class),
                clock: $app->make(Clock::class),
                newsRepository: $app->make(NewsRepository::class),
                forecastRepository: $app->make(ForecastRepository::class),
                avatarRepository: $app->make(AvatarRepository::class),
                avatarContextFrameGenerator: $app->make(AvatarContextFrameGenerator::class),
            );
        });

        $this->app->bind(RetrieveVideoContent::class, function ($app) {
            return new RetrieveVideoContent(
                videoContentRetriever: $app->make(VideoContentRetriever::class),
                videoRepository: $app->make(VideoRepository::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(VideoFileValidator::class, function ($app) {
            return new VideoFileValidatorImpl(
                fileSystem: $app->make(FileSystem::class)
            );
        });

        $this->app->bind(ApplyVoice::class, function ($app) {
            return new ApplyVoice(
                videoRepository: $app->make(VideoRepository::class),
                avatarRepository: $app->make(AvatarRepository::class),
                voiceRepository: $app->make(VoiceRepository::class),
                voiceGenerator: $app->make(VoiceGenerator::class),
                videoComposer: $app->make(VideoComposer::class),
                audioIsolator: $app->make(AudioIsolator::class),
            );
        });
    }

    private function registerClipServices(): void
    {
        $this->app->bind(VideoComposer::class, FfmpegVideoComposer::class);
        $this->app->bind(ClipRepository::class, EloquentClipRepository::class);

        $this->app->bind(ClipFactory::class, function ($app) {
            return new ClipFactory(
                clock: $app->make(Clock::class)
            );
        });

        $this->app->bind(ClipDownloader::class, function ($app) {
            return new VeoClipDownloader(
                apiKey: config('services.google.veo_api_key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class)
            );
        });

        $this->app->bind(CreateClip::class, function ($app) {
            return new CreateClip(
                videoRepository: $app->make(VideoRepository::class),
                clipRepository: $app->make(ClipRepository::class),
                clipFactory: $app->make(ClipFactory::class),
                eventBus: $app->make(EventBus::class),
                clock: $app->make(Clock::class),
                totalClips: (int) config('veo.total_clips', 5),
            );
        });

        $this->app->bind(GenerateClip::class, function ($app) {
            return new GenerateClip(
                clipRepository: $app->make(ClipRepository::class),
                videoRepository: $app->make(VideoRepository::class),
                videoGenerator: $app->make(VideoGenerator::class),
                videoExtender: $app->make(VideoExtender::class),
                videoPromptExtractor: $app->make(VideoPromptExtractor::class),
                scriptTranslator: $app->make(ScriptTranslator::class),
                avatarRepository: $app->make(AvatarRepository::class),
                eventBus: $app->make(EventBus::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(DownloadClip::class, function ($app) {
            return new DownloadClip(
                clipRepository: $app->make(ClipRepository::class),
                clipDownloader: $app->make(ClipDownloader::class),
                eventBus: $app->make(EventBus::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(ComposeShort::class, function ($app) {
            return new ComposeShort(
                clipRepository: $app->make(ClipRepository::class),
                videoRepository: $app->make(VideoRepository::class),
                clock: $app->make(Clock::class),
                avatarRepository: $app->make(AvatarRepository::class),
                voiceRepository: $app->make(VoiceRepository::class),
                voiceGenerator: $app->make(VoiceGenerator::class),
                videoComposer: $app->make(VideoComposer::class),
                mediaRepository: $app->make(MediaRepository::class),
            );
        });

        $this->registerClipEventHandlers();
    }

    private function registerClipEventHandlers(): void
    {
        /** @var EventHandlerRegistry $registry */
        $registry = $this->app->make(EventHandlerRegistry::class);

        $registry->register(VideoCreated::class, OnVideoCreatedHandler::class);
        $registry->register(ClipCreated::class, OnClipCreatedHandler::class);
        $registry->register(ClipGenerated::class, OnClipGeneratedHandler::class);
        $registry->register(ClipCompleted::class, OnClipCompletedHandler::class);
        $registry->register(AllClipsCompleted::class, OnAllClipsCompletedHandler::class);
    }

    private function registerAvatarServices(): void
    {
        $this->app->bind(AvatarRepository::class, function ($app) {
            return new EloquentAvatarRepository(
                clock: $app->make(Clock::class),
                mediaRepository: $app->make(MediaRepository::class)
            );
        });

        $this->app->bind(AvatarFactory::class, function ($app) {
            return new AvatarFactory(
                clock: $app->make(Clock::class)
            );
        });

        $this->app->bind(OpenAiAvatarRepository::class, function ($app) {
            return new OpenAiAvatarRepository(
                apiKey: config('services.openai.key') ?? '',
                mediaRepository: $app->make(MediaRepository::class),
                httpClient: $app->make(HttpClient::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(AvatarContextFrameGenerator::class, function ($app) {
            return new OpenAiAvatarContextFrameGenerator(
                apiKey: config('services.openai.key') ?? '',
                mediaRepository: $app->make(MediaRepository::class),
                httpClient: $app->make(HttpClient::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(CreateAvatarRequestMapper::class, CreateAvatarRequestMapper::class);

        $this->app->bind(CreateAvatar::class, function ($app) {
            return new CreateAvatar(
                avatarFactory: $app->make(AvatarFactory::class),
                avatarRepository: $app->make(AvatarRepository::class),
                mediaRepository: $app->make(MediaRepository::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(GenerateAvatarMetadata::class, function ($app) {
            return new GenerateAvatarMetadata(
                avatarRepository: $app->make(AvatarRepository::class),
                openAiAvatarRepository: $app->make(OpenAiAvatarRepository::class),
            );
        });

        $this->app->bind(GetAvatars::class, function ($app) {
            return new GetAvatars(
                avatarRepository: $app->make(AvatarRepository::class),
            );
        });

        $this->app->bind(GetAvatar::class, function ($app) {
            return new GetAvatar(
                avatarRepository: $app->make(AvatarRepository::class),
            );
        });

        $this->app->bind(DeleteAvatar::class, function ($app) {
            return new DeleteAvatar(
                avatarRepository: $app->make(AvatarRepository::class),
                mediaRepository: $app->make(MediaRepository::class),
            );
        });
    }

    private function registerImageServices(): void
    {
        $this->app->bind(ImageRepository::class, function ($app) {
            return new EloquentImageRepository();
        });

        $this->app->bind(ImageFactory::class, function ($app) {
            return new ImageFactory(
                clock: $app->make(Clock::class)
            );
        });

        $this->app->bind(GetImageFile::class, function ($app) {
            return new GetImageFile(
                imageRepository: $app->make(ImageRepository::class),
            );
        });

        $this->app->bind(MediaRepository::class, EloquentMediaRepository::class);
    }

    private function registerVoiceServices(): void
    {
        $this->app->bind(ElevenLabsTextToSpeech::class, function ($app) {
            return new ElevenLabsTextToSpeech(
                apiKey: config('services.elevenlabs.api_key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class),
                modelId: config('elevenlabs.tts_model_id'),
                outputFormat: config('elevenlabs.output_format'),
                timeout: config('elevenlabs.timeout'),
            );
        });

        $this->app->bind(VoiceRepository::class, function ($app) {
            return new ElevenLabsVoiceRepository(
                voiceCloner: $app->make(VoiceCloner::class),
                textToSpeech: $app->make(ElevenLabsTextToSpeech::class),
            );
        });

        $this->app->bind(VoiceGenerator::class, function ($app) {
            return new ElevenLabsVoiceGenerator(
                apiKey: config('services.elevenlabs.api_key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class),
                modelId: config('elevenlabs.model_id'),
                outputFormat: config('elevenlabs.output_format'),
                removeBackgroundNoise: config('elevenlabs.remove_background_noise'),
                timeout: config('elevenlabs.timeout'),
                stability: config('elevenlabs.stability'),
                similarityBoost: config('elevenlabs.similarity_boost'),
            );
        });

        $this->app->bind(AudioIsolator::class, function ($app) {
            return new ElevenLabsAudioIsolator(
                apiKey: config('services.elevenlabs.api_key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class),
                timeout: config('elevenlabs.timeout'),
            );
        });

        $this->app->bind(VoiceCloner::class, function ($app) {
            return new ElevenLabsVoiceCloner(
                apiKey: config('services.elevenlabs.api_key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class),
                timeout: config('elevenlabs.timeout'),
            );
        });

        $this->app->bind(CloneVoice::class, function ($app) {
            return new CloneVoice(
                voiceRepository: $app->make(VoiceRepository::class),
                clock: $app->make(Clock::class),
            );
        });
    }

    private function registerWeatherServices(): void
    {
        $this->app->singleton(AemetWeatherProvider::class, function ($app) {
            return new AemetWeatherProvider(
                apiKey: config('services.aemet.api_key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class),
            );
        });

        $this->app->bind(WeatherProvider::class, AemetWeatherProvider::class);
        $this->app->bind(ForecastRepository::class, EloquentForecastRepository::class);

        $this->app->bind(GetForecasts::class, function ($app) {
            return new GetForecasts(
                weatherProvider: $app->make(WeatherProvider::class),
                forecastRepository: $app->make(ForecastRepository::class),
                forecastSummarizer: $app->make(ForecastSummarizer::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(ForecastSummarizer::class, OpenAIForecastSummarizer::class);
    }

    private function registerNewsServices(): void
    {
        $this->app->bind(NewsRepository::class, EloquentNewsRepository::class);

        $this->app->bind(NewsProvider::class, function ($app) {
            return new TresDJuegosClient(
                httpClient: $app->make(HttpClient::class)
            );
        });

        $this->app->bind(DownloadNews::class, function ($app) {
            return new DownloadNews(
                newsProvider: $app->make(NewsProvider::class),
                newsRepository: $app->make(NewsRepository::class),
            );
        });
    }
}
