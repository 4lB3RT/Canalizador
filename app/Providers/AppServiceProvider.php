<?php

namespace App\Providers;

use App\Services\GoogleClientService;
use App\Services\GoogleTokenService;
use Canalizador\Avatar\Application\UseCases\CreateAvatar\CreateAvatar;
use Canalizador\Avatar\Domain\Factories\AvatarFactory;
use Canalizador\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\Avatar\Infrastructure\Http\Api\Mappers\CreateAvatarRequestMapper;
use Canalizador\Avatar\Infrastructure\Repositories\Eloquent\EloquentAvatarRepository;
use Canalizador\Avatar\Infrastructure\Repositories\OpenAI\OpenAiAvatarRepository;
use Canalizador\Channel\Application\UseCases\SyncChannel\SyncChannel;
use Canalizador\Channel\Application\UseCases\UpdateChannelWithAI\UpdateChannelWithAI;
use Canalizador\Channel\Domain\Repositories\ChannelMetadataRepository;
use Canalizador\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\Channel\Infrastructure\Repositories\Eloquent\EloquentChannelRepository;
use Canalizador\Channel\Infrastructure\Repositories\OpenAI\OpenAIChannelRepository;
use Canalizador\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use Canalizador\Image\Domain\Factories\ImageFactory;
use Canalizador\Image\Domain\Repositories\ImageRepository;
use Canalizador\Image\Infrastructure\Repositories\Eloquent\EloquentImageRepository;
use Canalizador\News\Application\UseCases\DownloadNews\DownloadNews;
use Canalizador\News\Domain\Repositories\NewsProvider;
use Canalizador\News\Domain\Repositories\NewsRepository;
use Canalizador\News\Infrastructure\Repositories\Eloquent\EloquentNewsRepository;
use Canalizador\News\Infrastructure\Repositories\TresDJuegos\TresDJuegosClient;
use Canalizador\Voice\Application\UseCases\CloneVoice\CloneVoice;
use Canalizador\Voice\Application\UseCases\GenerateVoice\GenerateVoice;
use Canalizador\Voice\Domain\Repositories\AudioIsolator;
use Canalizador\Voice\Domain\Repositories\VoiceCloner;
use Canalizador\Voice\Domain\Repositories\VoiceGenerator;
use Canalizador\Voice\Domain\Repositories\VoiceRepository;
use Canalizador\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsAudioIsolator;
use Canalizador\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsVoiceCloner;
use Canalizador\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsVoiceGenerator;
use Canalizador\Voice\Infrastructure\Repositories\Eloquent\EloquentVoiceRepository;
use Canalizador\Script\Domain\Factories\ScriptFactory;
use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Script\Infrastructure\Repositories\Eloquent\EloquentScriptRepository;
use Canalizador\Script\Infrastructure\Repositories\OpenAI\OpenAIScriptGenerator;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Infrastructure\Console\SetupRabbitMQCommand;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;
use Canalizador\Shared\Infrastructure\Events\EventHandlerRegistry;
use Canalizador\Shared\Infrastructure\Events\LaravelQueueEventBus;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Shared\Infrastructure\Services\GoogleYouTubeAnalyticsServiceFactory;
use Canalizador\Shared\Infrastructure\Services\HttpErrorExtractor;
use Canalizador\Shared\Infrastructure\Services\HttpResponseValidator as HttpResponseValidatorImpl;
use Canalizador\Shared\Infrastructure\Services\LaravelHttpClient;
use Canalizador\Shared\Infrastructure\Services\SystemClock;
use Canalizador\Clip\Application\Handlers\OnAllClipsCompletedHandler;
use Canalizador\Clip\Application\Handlers\OnClipCompletedHandler;
use Canalizador\Clip\Application\Handlers\OnClipCreatedHandler;
use Canalizador\Clip\Application\Handlers\OnClipGeneratedHandler;
use Canalizador\Clip\Application\Handlers\OnVideoCreatedHandler;
use Canalizador\Clip\Application\UseCases\ComposeShort\ComposeShort;
use Canalizador\Clip\Application\UseCases\CreateClip\CreateClip;
use Canalizador\Clip\Application\UseCases\DownloadClip\DownloadClip;
use Canalizador\Clip\Application\UseCases\GenerateClip\GenerateClip;
use Canalizador\Clip\Domain\Events\AllClipsCompleted;
use Canalizador\Clip\Domain\Events\ClipCompleted;
use Canalizador\Clip\Domain\Events\ClipCreated;
use Canalizador\Clip\Domain\Events\ClipGenerated;
use Canalizador\Clip\Domain\Factories\ClipFactory;
use Canalizador\Clip\Domain\Repositories\ClipDownloader;
use Canalizador\Clip\Domain\Repositories\ClipRepository;
use Canalizador\Clip\Infrastructure\Repositories\Eloquent\EloquentClipRepository;
use Canalizador\Clip\Infrastructure\Repositories\Veo\VeoClipDownloader;
use Canalizador\Clip\Domain\Services\VideoComposer;
use Canalizador\Clip\Infrastructure\Services\FfmpegVideoComposer;
use Canalizador\Video\Application\UseCases\ApplyVoice\ApplyVoice;
use Canalizador\Video\Application\UseCases\CreateVideo\CreateVideo;
use Canalizador\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\Video\Application\UseCases\RetrieveVideoContent\RetrieveVideoContent;
use Canalizador\Video\Domain\Events\VideoCreated;
use Canalizador\Video\Domain\Factories\VideoFactory;
use Canalizador\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\Video\Domain\Repositories\VideoExtender;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\Services\FileSystem;
use Canalizador\Video\Domain\Services\VideoFileValidator;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Domain\Services\YouTubeServiceFactory;
use Canalizador\Video\Infrastructure\Factories\VideoPublisherFactory as VideoPublisherFactoryImpl;
use Canalizador\Video\Infrastructure\Http\Api\Mappers\CreateVideoRequestMapper;
use Canalizador\Video\Infrastructure\Http\Api\Mappers\PublishVideoRequestMapper;
use Canalizador\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\OpenAI\OpenAIVideoMetadataGenerator;
use Canalizador\Video\Infrastructure\Repositories\Veo\VeoVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\YouTube\YoutubeVideoPublisher;
use Canalizador\Video\Infrastructure\Services\JsonVideoPromptExtractor;
use Canalizador\Video\Infrastructure\Services\LaravelFileSystem;
use Canalizador\Video\Infrastructure\Services\VideoFileValidator as VideoFileValidatorImpl;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeErrorExtractor;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeServiceFactory;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoBuilder;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoUploader;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeErrorExtractor;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;
use Canalizador\VideoLegacy\Application\UseCases\GetYoutubeVideo;
use Canalizador\VideoLegacy\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
use Canalizador\Weather\Application\UseCases\GetForecasts\GetForecasts;
use Canalizador\Weather\Domain\Repositories\ForecastRepository;
use Canalizador\Weather\Domain\Repositories\WeatherProvider;
use Canalizador\Weather\Infrastructure\Repositories\Aemet\AemetWeatherProvider;
use Canalizador\Weather\Infrastructure\Repositories\Eloquent\EloquentForecastRepository;
use Canalizador\Weather\Domain\Repositories\ForecastSummarizer;
use Canalizador\Weather\Infrastructure\Repositories\OpenAI\OpenAIForecastSummarizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerSharedServices();
        $this->registerScriptServices();
        $this->registerVideoServices();
        $this->registerClipServices();
        $this->registerChannelServices();
        $this->registerAvatarServices();
        $this->registerImageServices();
        $this->registerNewsServices();
        $this->registerVoiceServices();
        $this->registerWeatherServices();
    }

    public function boot(): void
    {
        $this->commands([
            SetupRabbitMQCommand::class,
        ]);
    }

    private function registerSharedServices(): void
    {
        $this->app->bind(Clock::class, SystemClock::class);
        $this->app->bind(HttpClient::class, LaravelHttpClient::class);

        $this->app->bind(HttpResponseValidator::class, function ($app) {
            return new HttpResponseValidatorImpl(
                errorExtractor: new HttpErrorExtractor()
            );
        });

        $this->app->bind(GoogleClientService::class, function ($app) {
            return new GoogleClientService(
                googleTokenService: $app->make(GoogleTokenService::class)
            );
        });

        $this->app->bind(YouTubeAnalyticsServiceFactory::class, GoogleYouTubeAnalyticsServiceFactory::class);

        $this->app->bind(YoutubeAnalyticsApiClient::class, function ($app) {
            return new YoutubeAnalyticsApiClient(
                googleClientService: $app->make(GoogleClientService::class),
                youtubeAnalyticsServiceFactory: $app->make(YouTubeAnalyticsServiceFactory::class)
            );
        });

        $this->app->bind(YoutubeDataApiClient::class, function ($app) {
            return new YoutubeDataApiClient(
                apiKey: config('services.youtube.api_key'),
                googleClientService: $app->make(GoogleClientService::class),
                youtubeServiceFactory: $app->make(YouTubeServiceFactory::class)
            );
        });

        $this->app->bind(EventBus::class, LaravelQueueEventBus::class);
        $this->app->singleton(EventHandlerRegistry::class, function ($app) {
            return new EventHandlerRegistry($app);
        });
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
                channelRepository: $app->make(YoutubeChannelRepository::class)
            );
        });
    }

    private function registerVideoServices(): void
    {
        $this->app->bind(CreateVideoRequestMapper::class, CreateVideoRequestMapper::class);
        $this->app->bind(PublishVideoRequestMapper::class, PublishVideoRequestMapper::class);
        $this->app->bind(VideoPromptExtractor::class, JsonVideoPromptExtractor::class);
        $this->app->bind(VideoMetadataGenerator::class, OpenAIVideoMetadataGenerator::class);
        $this->app->bind(FileSystem::class, LaravelFileSystem::class);
        $this->app->bind(YouTubeVideoBuilder::class, GoogleYouTubeVideoBuilder::class);
        $this->app->bind(YouTubeErrorExtractor::class, GoogleYouTubeErrorExtractor::class);
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

        $this->app->bind(YouTubeVideoUploader::class, function ($app) {
            return new GoogleYouTubeVideoUploader(
                fileSystem: $app->make(FileSystem::class)
            );
        });

        $this->app->bind(YoutubeVideoPublisher::class, function ($app) {
            return new YoutubeVideoPublisher(
                googleClientService: $app->make(GoogleClientService::class),
                videoFileValidator: $app->make(VideoFileValidator::class),
                youtubeVideoBuilder: $app->make(YouTubeVideoBuilder::class),
                youtubeVideoUploader: $app->make(YouTubeVideoUploader::class),
                youtubeServiceFactory: $app->make(YouTubeServiceFactory::class)
            );
        });

        $this->app->bind(VideoPublisherFactory::class, function ($app) {
            return new VideoPublisherFactoryImpl(
                youtubeVideoPublisher: $app->make(YoutubeVideoPublisher::class)
            );
        });

        $this->app->bind(PublishVideo::class, function ($app) {
            return new PublishVideo(
                videoRepository: $app->make(VideoRepository::class),
                videoPublisherFactory: $app->make(VideoPublisherFactory::class)
            );
        });

        $this->app->bind(GetYoutubeVideo::class, function ($app) {
            return new GetYoutubeVideo(
                externalVideoRepository: $app->make(YoutubeVideoRepository::class)
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

    private function registerChannelServices(): void
    {
        $this->app->bind(ChannelRepository::class, function ($app) {
            return new EloquentChannelRepository();
        });

        $this->app->bind(YoutubeChannelRepository::class, function ($app) {
            return new YoutubeChannelRepository(
                youtubeClient: $app->make(YoutubeDataApiClient::class)
            );
        });

        $this->app->bind(ChannelMetadataRepository::class, OpenAIChannelRepository::class);

        $this->app->bind(UpdateChannelWithAI::class, function ($app) {
            return new UpdateChannelWithAI(
                youtubeChannelRepository: $app->make(YoutubeChannelRepository::class),
                channelMetadataRepository: $app->make(ChannelMetadataRepository::class),
                channelRepository: $app->make(ChannelRepository::class)
            );
        });

        $this->app->bind(SyncChannel::class, function ($app) {
            return new SyncChannel(
                channelRepository: $app->make(ChannelRepository::class),
                youtubeChannelRepository: $app->make(YoutubeChannelRepository::class)
            );
        });
    }

    private function registerAvatarServices(): void
    {
        $this->app->bind(AvatarRepository::class, function ($app) {
            return new EloquentAvatarRepository(
                clock: $app->make(Clock::class),
                imageRepository: $app->make(ImageRepository::class)
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
                imageFactory: $app->make(ImageFactory::class),
                imageRepository: $app->make(ImageRepository::class),
                httpClient: $app->make(HttpClient::class)
            );
        });

        $this->app->bind(CreateAvatarRequestMapper::class, CreateAvatarRequestMapper::class);

        $this->app->bind(CreateAvatar::class, function ($app) {
            return new CreateAvatar(
                avatarFactory: $app->make(AvatarFactory::class),
                avatarRepository: $app->make(AvatarRepository::class),
                openAiAvatarRepository: $app->make(OpenAiAvatarRepository::class)
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
    }

    private function registerVoiceServices(): void
    {
        $this->app->bind(VoiceRepository::class, EloquentVoiceRepository::class);

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

        $this->app->bind(GenerateVoice::class, function ($app) {
            return new GenerateVoice(
                voiceGenerator: $app->make(VoiceGenerator::class),
                voiceRepository: $app->make(VoiceRepository::class),
                avatarRepository: $app->make(AvatarRepository::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(CloneVoice::class, function ($app) {
            return new CloneVoice(
                voiceCloner: $app->make(VoiceCloner::class),
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
