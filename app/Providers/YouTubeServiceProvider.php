<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\GoogleClientService;
use App\Services\GoogleTokenService;
use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Infrastructure\Events\EventHandlerRegistry;
use Helmreel\Shared\Video\Domain\Repositories\VideoMetadataGenerator;
use Helmreel\Shared\Video\Infrastructure\Repositories\OpenAI\OpenAIVideoMetadataGenerator;
use Helmreel\VideoProduction\Video\Domain\Services\FileSystem;
use Helmreel\YouTube\Channel\Application\UseCases\GetChannel\GetChannel;
use Helmreel\YouTube\Channel\Application\UseCases\GetChannels\GetChannels;
use Helmreel\YouTube\Channel\Application\UseCases\GetChannelVideos\GetChannelVideos;
use Helmreel\YouTube\Channel\Application\UseCases\RegisterChannel\RegisterChannel;
use Helmreel\YouTube\Channel\Application\UseCases\SyncChannel\SyncChannel;
use Helmreel\YouTube\Channel\Application\UseCases\UpdateChannel\UpdateChannel;
use Helmreel\YouTube\Channel\Application\UseCases\UpdateChannelWithAI\UpdateChannelWithAI;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelMetadataRepository;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Channel\Infrastructure\Commands\RegisterChannelCommand;
use Helmreel\YouTube\Channel\Infrastructure\Commands\SyncAutoChannelsCommand;
use Helmreel\YouTube\Channel\Infrastructure\Repositories\Eloquent\EloquentChannelRepository;
use Helmreel\YouTube\Channel\Infrastructure\Repositories\OpenAI\OpenAIChannelRepository;
use Helmreel\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use Helmreel\YouTube\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;
use Helmreel\YouTube\Shared\Domain\Services\YouTubeServiceFactory as YouTubeSharedServiceFactory;
use Helmreel\YouTube\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;
use Helmreel\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Helmreel\YouTube\Shared\Infrastructure\Services\GoogleYouTubeAnalyticsServiceFactory;
use Helmreel\YouTube\Shared\Infrastructure\Services\GoogleYouTubeServiceFactory;
use Helmreel\YouTube\Video\Application\Handlers\OnYouTubeShortCreatedHandler;
use Helmreel\YouTube\Video\Application\Handlers\OnYouTubeVideoCreatedHandler;
use Helmreel\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo\DownloadLatestChannelVideo;
use Helmreel\YouTube\Video\Application\UseCases\FragmentAndPublishVideo\FragmentAndPublishVideo;
use Helmreel\YouTube\Video\Application\UseCases\GenerateShort\GenerateShort;
use Helmreel\YouTube\Video\Application\UseCases\GetVideos\GetVideos;
use Helmreel\YouTube\Video\Application\UseCases\PublishVideo\PublishVideo;
use Helmreel\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo\SmartFragmentAndPublishVideo;
use Helmreel\YouTube\Video\Application\UseCases\SyncLastVideo\SyncLastVideo;
use Helmreel\YouTube\Video\Application\UseCases\SyncVideo\SyncVideo;
use Helmreel\YouTube\Video\Domain\Events\ShortCreated;
use Helmreel\YouTube\Video\Domain\Events\VideoCreated;
use Helmreel\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Helmreel\YouTube\Video\Domain\Repositories\AudioExtractor;
use Helmreel\YouTube\Video\Domain\Repositories\ChannelVideoFinder;
use Helmreel\YouTube\Video\Domain\Repositories\SmartVideoFragmenter;
use Helmreel\YouTube\Video\Domain\Repositories\VideoDownloader;
use Helmreel\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Helmreel\YouTube\Video\Domain\Repositories\VideoPublisher;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Helmreel\YouTube\Video\Infrastructure\Agents\AudioTranscriptor;
use Helmreel\YouTube\Video\Infrastructure\Agents\CartoonVideoMaker;
use Helmreel\YouTube\Video\Infrastructure\Agents\SmartVideoEditor;
use Helmreel\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;
use Helmreel\YouTube\Video\Infrastructure\Commands\GenerateShortCommand;
use Helmreel\YouTube\Video\Infrastructure\Commands\GetVideoCommand;
use Helmreel\YouTube\Video\Infrastructure\Commands\PublishVideoCommand;
use Helmreel\YouTube\Video\Infrastructure\Commands\SyncLastVideoCommand;
use Helmreel\YouTube\Video\Infrastructure\Commands\VideoAgentCommand;
use Helmreel\YouTube\Video\Infrastructure\Factories\VideoPublisherFactory as VideoPublisherFactoryImpl;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Mappers\FragmentAndPublishVideoRequestMapper;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Mappers\PublishVideoRequestMapper;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Mappers\SmartFragmentAndPublishVideoRequestMapper;
use Helmreel\YouTube\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Helmreel\YouTube\Video\Infrastructure\Repositories\Redis\RedisVideoRepository;
use Helmreel\YouTube\Video\Infrastructure\Repositories\YouTube\GoogleYouTubeChannelVideoFinder;
use Helmreel\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoPublisher;
use Helmreel\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoRepository;
use Helmreel\YouTube\Video\Infrastructure\Repositories\YouTube\YtDlpVideoDownloader;
use Helmreel\YouTube\Video\Infrastructure\Services\FfmpegAudioExtractor;
use Helmreel\YouTube\Video\Infrastructure\Services\FfmpegVideoFragmenter;
use Helmreel\YouTube\Video\Infrastructure\Services\OpenAIVideoTranscriber;
use Helmreel\YouTube\Video\Infrastructure\Services\PrismSmartVideoFragmenter;
use Helmreel\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeErrorExtractor;
use Helmreel\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoBuilder;
use Helmreel\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoUploader;
use Helmreel\YouTube\Video\Infrastructure\Services\YouTube\YouTubeErrorExtractor;
use Helmreel\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder as GoogleAPIVideoBuilder;
use Helmreel\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;
use Helmreel\YouTube\Video\Infrastructure\Tools\AudioExtractor as AudioExtractorTool;
use Helmreel\YouTube\Video\Infrastructure\Tools\AudioTranscription;
use Helmreel\YouTube\Video\Infrastructure\Tools\VideoCutter;
use Helmreel\YouTube\Video\Infrastructure\Tools\VideoDownloader as VideoDownloaderTool;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class YouTubeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerGoogleServices();
        $this->registerChannelServices();
        $this->registerVideoServices();
        $this->registerVideoEventHandlers();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateShortCommand::class,
                GetVideoCommand::class,
                PublishVideoCommand::class,
                RegisterChannelCommand::class,
                SyncAutoChannelsCommand::class,
                SyncLastVideoCommand::class,
                VideoAgentCommand::class,
            ]);
        }
    }

    private function registerGoogleServices(): void
    {
        $this->app->bind(GoogleClientService::class, function ($app) {
            return new GoogleClientService(
                googleTokenService: $app->make(GoogleTokenService::class),
            );
        });

        $this->app->bind(YouTubeAnalyticsServiceFactory::class, GoogleYouTubeAnalyticsServiceFactory::class);
        $this->app->bind(YouTubeSharedServiceFactory::class, GoogleYouTubeServiceFactory::class);

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
                youtubeServiceFactory: $app->make(YouTubeSharedServiceFactory::class)
            );
        });
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

        $this->app->bind(RegisterChannel::class, function ($app) {
            return new RegisterChannel(
                externalChannelRepository: $app->make(YoutubeChannelRepository::class),
                internalChannelRepository: $app->make(EloquentChannelRepository::class),
            );
        });

        $this->app->bind(GetChannels::class, function ($app) {
            return new GetChannels(
                channelRepository: $app->make(ChannelRepository::class)
            );
        });

        $this->app->bind(GetChannel::class, function ($app) {
            return new GetChannel(
                channelRepository: $app->make(ChannelRepository::class)
            );
        });

        $this->app->bind(UpdateChannel::class, function ($app) {
            return new UpdateChannel(
                channelRepository: $app->make(ChannelRepository::class),
                youtubeChannelRepository: $app->make(YoutubeChannelRepository::class)
            );
        });
    }

    private function registerVideoServices(): void
    {
        $this->app->bind(PublishVideoRequestMapper::class, PublishVideoRequestMapper::class);
        $this->app->bind(FragmentAndPublishVideoRequestMapper::class, FragmentAndPublishVideoRequestMapper::class);
        $this->app->bind(GoogleAPIVideoBuilder::class, GoogleYouTubeVideoBuilder::class);
        $this->app->bind(YouTubeErrorExtractor::class, GoogleYouTubeErrorExtractor::class);
        $this->app->bind(YouTubeVideoUploader::class, function ($app) {
            return new GoogleYouTubeVideoUploader(
                fileSystem: $app->make(FileSystem::class)
            );
        });

        $this->app->bind(YoutubeVideoPublisher::class, function ($app) {
            return new YoutubeVideoPublisher(
                googleClientService:   $app->make(GoogleClientService::class),
                youtubeVideoBuilder:   $app->make(GoogleAPIVideoBuilder::class),
                youtubeVideoUploader:  $app->make(YouTubeVideoUploader::class),
                youtubeServiceFactory: $app->make(YouTubeSharedServiceFactory::class),
                channelRepository:     $app->make(ChannelRepository::class),
                videoRepository:       $app->make(VideoRepository::class),
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
                internalVideoRepository: $app->make(EloquentVideoRepository::class),
                externalVideoRepository: $app->make(YoutubeVideoRepository::class),
                videoPublisherFactory:   $app->make(VideoPublisherFactory::class),
                clock:                   $app->make(Clock::class),
            );
        });

        $this->app->bind(ChannelVideoFinder::class, function ($app) {
            return new GoogleYouTubeChannelVideoFinder(
                googleClientService: $app->make(GoogleClientService::class),
                youtubeServiceFactory: $app->make(YouTubeSharedServiceFactory::class)
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

        $this->app->bind(GetChannelVideos::class, function ($app) {
            return new GetChannelVideos(
                channelRepository: $app->make(ChannelRepository::class),
                videoRepository:   $app->make(VideoRepository::class),
            );
        });

        $this->app->bind(GetVideos::class, function ($app) {
            return new GetVideos(
                videoRepository: $app->make(VideoRepository::class),
            );
        });

        $this->app->bind(FragmentAndPublishVideo::class, function ($app) {
            return new FragmentAndPublishVideo(
                videoFragmenter: $app->make(VideoFragmenter::class),
                videoPublisherFactory: $app->make(VideoPublisherFactory::class),
            );
        });

        $this->app->bind(VideoRepository::class, EloquentVideoRepository::class);

        $this->app->bind(RedisVideoRepository::class, function () {
            return new RedisVideoRepository(
                redis: Redis::connection(),
            );
        });

        $this->app->bind(YoutubeVideoRepository::class, function ($app) {
            return new YoutubeVideoRepository(
                youtubeClient:        $app->make(YoutubeDataApiClient::class),
                channelRepository:    $app->make(ChannelRepository::class),
                localVideoRepository: $app->make(EloquentVideoRepository::class),
            );
        });

        $this->app->bind(YouTubeVideoBuilder::class, function ($app) {
            return new YouTubeVideoBuilder(
                localRepository: $app->make(EloquentVideoRepository::class),
                youtubeRepository: $app->make(YoutubeVideoRepository::class),
                videoDownloader:        $app->make(VideoDownloader::class),
                audioExtractor:         $app->make(AudioExtractor::class),
                videoTranscriber:       $app->make(VideoTranscriber::class),
                videoMetadataGenerator: $app->make(VideoMetadataGenerator::class),
                clock: $app->make(Clock::class),
            );
        });

        $this->app->bind(AudioExtractor::class, FfmpegAudioExtractor::class);
        $this->app->bind(VideoTranscriber::class, OpenAIVideoTranscriber::class);

        $this->app->bind(SmartVideoEditor::class, function ($app) {
            return new SmartVideoEditor(
                videoCutter: $app->make(VideoCutter::class),
            );
        });

        $this->app->bind(SmartVideoFragmenter::class, function ($app) {
            return new PrismSmartVideoFragmenter(
                smartVideoEditor: $app->make(SmartVideoEditor::class),
            );
        });

        $this->app->bind(SmartFragmentAndPublishVideoRequestMapper::class, SmartFragmentAndPublishVideoRequestMapper::class);

        $this->app->bind(SmartFragmentAndPublishVideo::class, function ($app) {
            return new SmartFragmentAndPublishVideo(
                videoRepository:      $app->make(VideoRepository::class),
                audioExtractor:       $app->make(AudioExtractor::class),
                videoTranscriber:     $app->make(VideoTranscriber::class),
                smartVideoFragmenter: $app->make(SmartVideoFragmenter::class),
                videoPublisherFactory: $app->make(VideoPublisherFactory::class),
            );
        });

        $this->app->bind(VideoDownloaderTool::class, function ($app) {
            return new VideoDownloaderTool(
                videoDownloader: $app->make(VideoDownloader::class),
            );
        });

        $this->app->bind(AudioExtractorTool::class, function ($app) {
            return new AudioExtractorTool(
                audioExtractor: $app->make(AudioExtractor::class),
            );
        });

        $this->app->bind(AudioTranscriptor::class, function ($app) {
            return new AudioTranscriptor(
                videoDownloader:    $app->make(VideoDownloaderTool::class),
                audioExtractor:     $app->make(AudioExtractorTool::class),
                audioTranscription: $app->make(AudioTranscription::class),
            );
        });

        $this->app->bind(CartoonVideoMaker::class, function ($app) {
            return new CartoonVideoMaker(
                audioExtractor: $app->make(AudioExtractorTool::class),
            );
        });

        $this->app->bind(VideoMetadataGenerator::class, OpenAIVideoMetadataGenerator::class);

        $this->app->bind(SyncLastVideo::class, function ($app) {
            return new SyncLastVideo(
                channelRepository: $app->make(ChannelRepository::class),
                internalVideoRepository: $app->make(EloquentVideoRepository::class),
                externalVideoRepository: $app->make(YoutubeVideoRepository::class),
                videoBuilder: $app->make(YouTubeVideoBuilder::class),
                eventBus: $app->make(EventBus::class),
            );
        });

        $this->app->bind(SyncVideo::class, function ($app) {
            return new SyncVideo(
                channelRepository:       $app->make(ChannelRepository::class),
                internalVideoRepository: $app->make(EloquentVideoRepository::class),
                externalVideoRepository: $app->make(YoutubeVideoRepository::class),
                videoBuilder:            $app->make(YouTubeVideoBuilder::class),
                eventBus:                $app->make(EventBus::class),
            );
        });

        $this->app->bind(GenerateShort::class, function ($app) {
            return new GenerateShort(
                videoBuilder: $app->make(YouTubeVideoBuilder::class),
                videoRepository: $app->make(EloquentVideoRepository::class),
                videoFragmenter: $app->make(VideoFragmenter::class),
                eventBus: $app->make(EventBus::class),
            );
        });
    }

    private function registerVideoEventHandlers(): void
    {
        /** @var EventHandlerRegistry $registry */
        $registry = $this->app->make(EventHandlerRegistry::class);

        $registry->register(VideoCreated::class, OnYouTubeVideoCreatedHandler::class);
        $registry->register(ShortCreated::class, OnYouTubeShortCreatedHandler::class);
    }
}
