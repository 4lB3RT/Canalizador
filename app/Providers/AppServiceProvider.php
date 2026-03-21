<?php

namespace App\Providers;

use App\Services\GoogleClientService;
use App\Services\GoogleTokenService;
use Canalizador\VideoProduction\Avatar\Application\UseCases\CreateAvatar\CreateAvatar;
use Canalizador\VideoProduction\Avatar\Domain\Factories\AvatarFactory;
use Canalizador\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\VideoProduction\Avatar\Infrastructure\Http\Api\Mappers\CreateAvatarRequestMapper;
use Canalizador\VideoProduction\Avatar\Infrastructure\Repositories\Eloquent\EloquentAvatarRepository;
use Canalizador\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI\OpenAiAvatarRepository;
use Canalizador\YouTube\Channel\Application\UseCases\SyncChannel\SyncChannel;
use Canalizador\YouTube\Channel\Application\UseCases\UpdateChannelWithAI\UpdateChannelWithAI;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelMetadataRepository;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\Eloquent\EloquentChannelRepository;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\OpenAI\OpenAIChannelRepository;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use Canalizador\VideoProduction\Image\Domain\Factories\ImageFactory;
use Canalizador\VideoProduction\Image\Domain\Repositories\ImageRepository;
use Canalizador\VideoProduction\Image\Infrastructure\Repositories\Eloquent\EloquentImageRepository;
use Canalizador\VideoProduction\News\Application\UseCases\DownloadNews\DownloadNews;
use Canalizador\VideoProduction\News\Domain\Repositories\NewsProvider;
use Canalizador\VideoProduction\News\Domain\Repositories\NewsRepository;
use Canalizador\VideoProduction\News\Infrastructure\Repositories\Eloquent\EloquentNewsRepository;
use Canalizador\VideoProduction\News\Infrastructure\Repositories\TresDJuegos\TresDJuegosClient;
use Canalizador\VideoProduction\Voice\Application\UseCases\CloneVoice\CloneVoice;
use Canalizador\VideoProduction\Voice\Application\UseCases\GenerateVoice\GenerateVoice;
use Canalizador\VideoProduction\Voice\Domain\Repositories\AudioIsolator;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceCloner;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceGenerator;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Canalizador\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsAudioIsolator;
use Canalizador\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsVoiceCloner;
use Canalizador\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs\ElevenLabsVoiceGenerator;
use Canalizador\VideoProduction\Voice\Infrastructure\Repositories\Eloquent\EloquentVoiceRepository;
use Canalizador\VideoProduction\Script\Domain\Factories\ScriptFactory;
use Canalizador\VideoProduction\Script\Domain\Repositories\ScriptGenerator;
use Canalizador\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Canalizador\VideoProduction\Script\Domain\Services\GenerateScript;
use Canalizador\VideoProduction\Script\Infrastructure\Repositories\Eloquent\EloquentScriptRepository;
use Canalizador\VideoProduction\Script\Infrastructure\Repositories\OpenAI\OpenAIScriptGenerator;
use Canalizador\VideoProduction\Shared\Domain\Events\EventBus;
use Canalizador\VideoProduction\Shared\Infrastructure\Console\SetupRabbitMQCommand;
use Canalizador\VideoProduction\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Shared\Domain\Services\HttpClient;
use Canalizador\VideoProduction\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\VideoProduction\Shared\Infrastructure\Events\EventHandlerRegistry;
use Canalizador\VideoProduction\Shared\Infrastructure\Events\LaravelQueueEventBus;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\YouTube\Shared\Infrastructure\Services\GoogleYouTubeAnalyticsServiceFactory;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\HttpErrorExtractor;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\HttpResponseValidator as HttpResponseValidatorImpl;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\LaravelHttpClient;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\SystemClock;
use Canalizador\VideoProduction\Clip\Application\Handlers\OnAllClipsCompletedHandler;
use Canalizador\VideoProduction\Clip\Application\Handlers\OnClipCompletedHandler;
use Canalizador\VideoProduction\Clip\Application\Handlers\OnClipCreatedHandler;
use Canalizador\VideoProduction\Clip\Application\Handlers\OnClipGeneratedHandler;
use Canalizador\VideoProduction\Clip\Application\Handlers\OnVideoCreatedHandler;
use Canalizador\VideoProduction\Clip\Application\UseCases\ComposeShort\ComposeShort;
use Canalizador\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClip;
use Canalizador\VideoProduction\Clip\Application\UseCases\DownloadClip\DownloadClip;
use Canalizador\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClip;
use Canalizador\VideoProduction\Clip\Domain\Events\AllClipsCompleted;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipCompleted;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipCreated;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipGenerated;
use Canalizador\VideoProduction\Clip\Domain\Factories\ClipFactory;
use Canalizador\VideoProduction\Clip\Domain\Repositories\ClipDownloader;
use Canalizador\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Canalizador\VideoProduction\Clip\Infrastructure\Repositories\Eloquent\EloquentClipRepository;
use Canalizador\VideoProduction\Clip\Infrastructure\Repositories\Veo\VeoClipDownloader;
use Canalizador\VideoProduction\Clip\Domain\Services\VideoComposer;
use Canalizador\VideoProduction\Clip\Infrastructure\Services\FfmpegVideoComposer;
use Canalizador\VideoProduction\Video\Application\UseCases\ApplyVoice\ApplyVoice;
use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\CreateVideo;
use Canalizador\VideoProduction\Video\Application\UseCases\RetrieveVideoContent\RetrieveVideoContent;
use Canalizador\VideoProduction\Video\Domain\Events\VideoCreated;
use Canalizador\VideoProduction\Video\Domain\Factories\VideoFactory;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoExtender;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoGenerator;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\Video\Domain\Services\FileSystem;
use Canalizador\VideoProduction\Video\Domain\Services\VideoFileValidator;
use Canalizador\VideoProduction\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\VideoProduction\Video\Domain\Services\YouTubeServiceFactory;
use Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Mappers\CreateVideoRequestMapper;
use Canalizador\VideoProduction\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Canalizador\VideoProduction\Video\Infrastructure\Repositories\OpenAI\OpenAIVideoMetadataGenerator;
use Canalizador\VideoProduction\Video\Infrastructure\Repositories\Veo\VeoVideoRepository;
use Canalizador\VideoProduction\Video\Infrastructure\Services\JsonVideoPromptExtractor;
use Canalizador\VideoProduction\Video\Infrastructure\Services\LaravelFileSystem;
use Canalizador\VideoProduction\Video\Infrastructure\Services\VideoFileValidator as VideoFileValidatorImpl;
use Canalizador\VideoProduction\Video\Infrastructure\Services\YouTube\GoogleYouTubeServiceFactory;
use Canalizador\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo\DownloadLatestChannelVideo;
use Canalizador\YouTube\Video\Application\UseCases\FragmentAndPublishVideo\FragmentAndPublishVideo;
use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\ChannelVideoFinder;
use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoPublisher;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeServiceFactory as YouTubeServiceFactoryYouTubeBC;
use Canalizador\YouTube\Shared\Infrastructure\Services\GoogleYouTubeServiceFactory as YouTubeGoogleServiceFactory;
use Canalizador\YouTube\Video\Infrastructure\Factories\VideoPublisherFactory as VideoPublisherFactoryImpl;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\FragmentAndPublishVideoRequestMapper;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\PublishVideoRequestMapper;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\GoogleYouTubeChannelVideoFinder;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoPublisher;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\YtDlpVideoDownloader;
use Canalizador\YouTube\Video\Infrastructure\Services\FfmpegVideoFragmenter;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeErrorExtractor;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoBuilder;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoUploader;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeErrorExtractor;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;
use Canalizador\VideoProduction\VideoLegacy\Application\UseCases\GetYoutubeVideo;
use Canalizador\VideoProduction\VideoLegacy\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
use Canalizador\VideoProduction\Weather\Application\UseCases\GetForecasts\GetForecasts;
use Canalizador\VideoProduction\Weather\Domain\Repositories\ForecastRepository;
use Canalizador\VideoProduction\Weather\Domain\Repositories\WeatherProvider;
use Canalizador\VideoProduction\Weather\Infrastructure\Repositories\Aemet\AemetWeatherProvider;
use Canalizador\VideoProduction\Weather\Infrastructure\Repositories\Eloquent\EloquentForecastRepository;
use Canalizador\VideoProduction\Weather\Domain\Repositories\ForecastSummarizer;
use Canalizador\VideoProduction\Weather\Infrastructure\Repositories\OpenAI\OpenAIForecastSummarizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerSharedServices();
        $this->registerScriptServices();
        $this->registerVideoProductionServices();
        $this->registerYouTubeServices();
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
                youtubeServiceFactory: $app->make(YouTubeServiceFactoryYouTubeBC::class)
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

    private function registerVideoProductionServices(): void
    {
        $this->app->bind(CreateVideoRequestMapper::class, CreateVideoRequestMapper::class);
        $this->app->bind(VideoPromptExtractor::class, JsonVideoPromptExtractor::class);
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

        $this->app->bind(GetYoutubeVideo::class, function ($app) {
            return new GetYoutubeVideo(
                externalVideoRepository: $app->make(YoutubeVideoRepository::class)
            );
        });
    }

    private function registerYouTubeServices(): void
    {
        $this->app->bind(PublishVideoRequestMapper::class, PublishVideoRequestMapper::class);
        $this->app->bind(FragmentAndPublishVideoRequestMapper::class, FragmentAndPublishVideoRequestMapper::class);
        $this->app->bind(YouTubeVideoBuilder::class, GoogleYouTubeVideoBuilder::class);
        $this->app->bind(YouTubeErrorExtractor::class, GoogleYouTubeErrorExtractor::class);
        $this->app->bind(YouTubeServiceFactoryYouTubeBC::class, YouTubeGoogleServiceFactory::class);

        $this->app->bind(YouTubeVideoUploader::class, function ($app) {
            return new GoogleYouTubeVideoUploader(
                fileSystem: $app->make(FileSystem::class)
            );
        });

        $this->app->bind(YoutubeVideoPublisher::class, function ($app) {
            return new YoutubeVideoPublisher(
                googleClientService: $app->make(GoogleClientService::class),
                youtubeVideoBuilder: $app->make(YouTubeVideoBuilder::class),
                youtubeVideoUploader: $app->make(YouTubeVideoUploader::class),
                youtubeServiceFactory: $app->make(YouTubeServiceFactoryYouTubeBC::class)
            );
        });

        $this->app->bind(VideoPublisher::class, YoutubeVideoPublisher::class);

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

        $this->app->bind(ChannelVideoFinder::class, function ($app) {
            return new GoogleYouTubeChannelVideoFinder(
                googleClientService: $app->make(GoogleClientService::class),
                youtubeServiceFactory: $app->make(YouTubeServiceFactoryYouTubeBC::class)
            );
        });

        $this->app->bind(VideoDownloader::class, YtDlpVideoDownloader::class);

        $this->app->bind(VideoFragmenter::class, FfmpegVideoFragmenter::class);

        $this->app->bind(DownloadLatestChannelVideo::class, function ($app) {
            return new DownloadLatestChannelVideo(
                channelVideoFinder: $app->make(ChannelVideoFinder::class),
                videoDownloader: $app->make(VideoDownloader::class),
            );
        });

        $this->app->bind(FragmentAndPublishVideo::class, function ($app) {
            return new FragmentAndPublishVideo(
                videoFragmenter: $app->make(VideoFragmenter::class),
                videoPublisherFactory: $app->make(VideoPublisherFactory::class),
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
