<?php

namespace App\Providers;

use App\Services\GoogleClientService;
use App\Services\GoogleTokenService;
use Canalizador\Script\Domain\Factories\ScriptFactory;
use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Canalizador\Script\Domain\Repositories\ScriptIdeaGenerator;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Script\Infrastructure\Repositories\Eloquent\EloquentScriptRepository;
use Canalizador\Script\Infrastructure\Repositories\OpenAI\OpenAIScriptGenerator;
use Canalizador\Script\Infrastructure\Repositories\OpenAI\OpenAIScriptIdeaGenerator;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Shared\Infrastructure\Services\GoogleYouTubeAnalyticsServiceFactory;
use Canalizador\Shared\Infrastructure\Services\HttpErrorExtractor;
use Canalizador\Shared\Infrastructure\Services\HttpResponseValidator as HttpResponseValidatorImpl;
use Canalizador\Shared\Infrastructure\Services\LaravelHttpClient;
use Canalizador\Shared\Infrastructure\Services\SystemClock;
use Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs\ElevenlabsTranscriptionRepository;
use Canalizador\Video\Application\UseCases\GenerateVideo\GenerateVideo;
use Canalizador\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\Video\Application\UseCases\RetrieveVideoContent\RetrieveVideoContent;
use Canalizador\Video\Domain\Factories\VideoFactory;
use Canalizador\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Infrastructure\Factories\VideoPublisherFactory as VideoPublisherFactoryImpl;
use Canalizador\Video\Infrastructure\Http\Api\Mappers\GenerateVideoRequestMapper;
use Canalizador\Video\Infrastructure\Http\Api\Mappers\PublishVideoRequestMapper;
use Canalizador\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\FFmpeg\FFmpegVideoComposer;
use Canalizador\Video\Infrastructure\Repositories\Luma\LumaVideoGenerator;
use Canalizador\Video\Infrastructure\Repositories\OpenAI\OpenAITextToSpeechGenerator;
use Canalizador\Video\Infrastructure\Repositories\OpenAI\OpenAIVideoMetadataGenerator;
use Canalizador\Video\Infrastructure\Repositories\Sora\SoraVideoRepository;
use Canalizador\Video\Infrastructure\Repositories\YouTube\YoutubeVideoPublisher;
use Canalizador\Video\Infrastructure\Services\JsonVideoPromptExtractor;
use Canalizador\Video\Domain\Services\FileSystem;
use Canalizador\Video\Domain\Services\VideoFileValidator;
use Canalizador\Video\Domain\Services\VideoMetadataExtractor;
use Canalizador\Video\Infrastructure\Services\LaravelFileSystem;
use Canalizador\Video\Infrastructure\Services\VideoFileValidator as VideoFileValidatorImpl;
use Canalizador\Video\Infrastructure\Services\VideoMetadataExtractor as VideoMetadataExtractorImpl;
use Canalizador\Video\Domain\Services\YouTubeServiceFactory;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeErrorExtractor;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeServiceFactory;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoBuilder;
use Canalizador\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoUploader;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeErrorExtractor;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;
use Canalizador\VideoLegacy\Application\UseCases\GetYoutubeVideo;
use Canalizador\VideoLegacy\Domain\Repositories\VideoRepository as VideoLegacyRepository;
use Canalizador\VideoLegacy\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Canalizador\VideoLegacy\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;
use Canalizador\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\Channel\Domain\Repositories\ChannelMetadataRepository;
use Canalizador\Channel\Application\UseCases\SyncChannel\SyncChannel;
use Canalizador\Channel\Application\UseCases\UpdateChannelWithAI\UpdateChannelWithAI;
use Canalizador\Channel\Infrastructure\Repositories\Eloquent\EloquentChannelRepository;
use Canalizador\Channel\Infrastructure\Repositories\OpenAI\OpenAIChannelRepository;
use Canalizador\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
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

            return new YoutubeDataApiClient(
                apiKey: $apiKey,
                googleClientService: $app->make(GoogleClientService::class),
                youtubeServiceFactory: $app->make(YouTubeServiceFactory::class)
            );
        });

        $this->app->bind(GetYoutubeVideo::class, function ($app) {
            return new GetYoutubeVideo(
                externalVideoRepository: $app->make(YoutubeVideoRepository::class)
            );
        });

        $this->app->bind(Clock::class, SystemClock::class);

        $this->app->bind(HttpClient::class, LaravelHttpClient::class);

        $this->app->bind(HttpResponseValidator::class, function ($app) {
            return new HttpResponseValidatorImpl(
                errorExtractor: new HttpErrorExtractor()
            );
        });

        $this->app->bind(GenerateVideoRequestMapper::class, GenerateVideoRequestMapper::class);

        $this->app->bind(PublishVideoRequestMapper::class, PublishVideoRequestMapper::class);

        $this->app->bind(ScriptFactory::class, ScriptFactory::class);

        $this->app->bind(ScriptIdeaGenerator::class, OpenAIScriptIdeaGenerator::class);

        $this->app->bind(ScriptGenerator::class, OpenAIScriptGenerator::class);

        $this->app->bind(GenerateScript::class, function ($app) {
            return new GenerateScript(
                scriptRepository: $app->make(EloquentScriptRepository::class),
                scriptGenerator: $app->make(ScriptGenerator::class),
                scriptIdeaGenerator: $app->make(ScriptIdeaGenerator::class),
                scriptFactory: $app->make(ScriptFactory::class)
            );
        });

        $this->app->bind(ScriptRepository::class, EloquentScriptRepository::class);

        $this->app->bind(VideoRepository::class, function ($app) {
            return new EloquentVideoRepository(
                scriptRepository: $app->make(ScriptRepository::class)
            );
        });

        $this->app->bind(VideoGenerator::class, function ($app) {
            return new SoraVideoRepository(
                    apiKey: config('services.openai.key') ?? '',
                    httpClient: $app->make(HttpClient::class),
                    responseValidator: $app->make(HttpResponseValidator::class)
            );
        });

        $this->app->bind(VideoPromptExtractor::class, JsonVideoPromptExtractor::class);

        $this->app->bind(VideoMetadataGenerator::class, OpenAIVideoMetadataGenerator::class);

        $this->app->bind(VideoFactory::class, function ($app) {
            return new VideoFactory(
                clock: $app->make(Clock::class)
            );
        });

        $this->app->bind(GenerateVideo::class, function ($app) {
            return new GenerateVideo(
                scriptRepository: $app->make(ScriptRepository::class),
                generateScript: $app->make(GenerateScript::class),
                videoPromptExtractor: $app->make(VideoPromptExtractor::class),
                videoGenerator: $app->make(VideoGenerator::class),
                videoFactory: $app->make(VideoFactory::class),
                videoRepository: $app->make(VideoRepository::class),
                videoMetadataGenerator: $app->make(VideoMetadataGenerator::class),
            );
        });

        $this->app->bind(VideoContentRetriever::class, function ($app) {
            return new SoraVideoRepository(
                apiKey: config('services.openai.key') ?? '',
                httpClient: $app->make(HttpClient::class),
                responseValidator: $app->make(HttpResponseValidator::class)
            );
        });

        $this->app->bind(RetrieveVideoContent::class, function ($app) {
            return new RetrieveVideoContent(
                videoContentRetriever: $app->make(VideoContentRetriever::class),
                videoRepository: $app->make(VideoRepository::class),
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

        $this->app->bind(FileSystem::class, LaravelFileSystem::class);

        $this->app->bind(VideoFileValidator::class, function ($app) {
            return new VideoFileValidatorImpl(
                fileSystem: $app->make(FileSystem::class)
            );
        });

        $this->app->bind(YouTubeVideoBuilder::class, GoogleYouTubeVideoBuilder::class);

        $this->app->bind(YouTubeVideoUploader::class, function ($app) {
            return new GoogleYouTubeVideoUploader(
                fileSystem: $app->make(FileSystem::class)
            );
        });

        $this->app->bind(YouTubeErrorExtractor::class, GoogleYouTubeErrorExtractor::class);

        $this->app->bind(YouTubeServiceFactory::class, GoogleYouTubeServiceFactory::class);

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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
