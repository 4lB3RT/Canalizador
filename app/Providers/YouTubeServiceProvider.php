<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\GoogleClientService;
use App\Services\GoogleTokenService;
use Canalizador\VideoProduction\Video\Domain\Services\FileSystem;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;
use Canalizador\YouTube\Shared\Infrastructure\Services\GoogleYouTubeAnalyticsServiceFactory;
use Canalizador\YouTube\Shared\Infrastructure\Services\GoogleYouTubeServiceFactory;
use Canalizador\YouTube\Channel\Application\UseCases\SyncChannel\SyncChannel;
use Canalizador\YouTube\Channel\Application\UseCases\UpdateChannelWithAI\UpdateChannelWithAI;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelMetadataRepository;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\Eloquent\EloquentChannelRepository;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\OpenAI\OpenAIChannelRepository;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeServiceFactory as YouTubeSharedServiceFactory;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo\DownloadLatestChannelVideo;
use Canalizador\YouTube\Video\Application\UseCases\FragmentAndPublishVideo\FragmentAndPublishVideo;
use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo\SmartFragmentAndPublishVideo;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\Repositories\ChannelVideoFinder;
use Canalizador\YouTube\Video\Domain\Repositories\SmartVideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoPublisher;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Infrastructure\Agents\AudioTranscriptor;
use Canalizador\YouTube\Video\Infrastructure\Agents\CartoonVideoMaker;
use Canalizador\YouTube\Video\Infrastructure\Agents\SmartVideoEditor;
use Canalizador\YouTube\Video\Infrastructure\Commands\VideoAgentCommand;
use Canalizador\YouTube\Video\Infrastructure\Factories\VideoPublisherFactory as VideoPublisherFactoryImpl;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\FragmentAndPublishVideoRequestMapper;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\PublishVideoRequestMapper;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\SmartFragmentAndPublishVideoRequestMapper;
use Canalizador\YouTube\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\GoogleYouTubeChannelVideoFinder;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoPublisher;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\YtDlpVideoDownloader;
use Canalizador\YouTube\Video\Infrastructure\Services\FfmpegAudioExtractor;
use Canalizador\YouTube\Video\Infrastructure\Services\FfmpegVideoFragmenter;
use Canalizador\YouTube\Video\Infrastructure\Services\OpenAIVideoTranscriber;
use Canalizador\YouTube\Video\Infrastructure\Services\PrismSmartVideoFragmenter;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeErrorExtractor;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoBuilder;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\GoogleYouTubeVideoUploader;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeErrorExtractor;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;
use Canalizador\YouTube\Video\Infrastructure\Tools\AudioExtractor as AudioExtractorTool;
use Canalizador\YouTube\Video\Infrastructure\Tools\AudioTranscription;
use Canalizador\YouTube\Video\Infrastructure\Tools\VideoCutter;
use Canalizador\YouTube\Video\Infrastructure\Tools\VideoDownloader as VideoDownloaderTool;
use Illuminate\Support\ServiceProvider;

class YouTubeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerGoogleServices();
        $this->registerChannelServices();
        $this->registerVideoServices();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([VideoAgentCommand::class]);
        }
    }

    private function registerGoogleServices(): void
    {
        $this->app->bind(GoogleClientService::class, function ($app) {
            return new GoogleClientService(
                googleTokenService: $app->make(GoogleTokenService::class)
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
    }

    private function registerVideoServices(): void
    {
        $this->app->bind(PublishVideoRequestMapper::class, PublishVideoRequestMapper::class);
        $this->app->bind(FragmentAndPublishVideoRequestMapper::class, FragmentAndPublishVideoRequestMapper::class);
        $this->app->bind(YouTubeVideoBuilder::class, GoogleYouTubeVideoBuilder::class);
        $this->app->bind(YouTubeErrorExtractor::class, GoogleYouTubeErrorExtractor::class);
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
                youtubeServiceFactory: $app->make(YouTubeSharedServiceFactory::class)
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

        $this->app->bind(FragmentAndPublishVideo::class, function ($app) {
            return new FragmentAndPublishVideo(
                videoFragmenter: $app->make(VideoFragmenter::class),
                videoPublisherFactory: $app->make(VideoPublisherFactory::class),
            );
        });

        $this->app->bind(VideoRepository::class, EloquentVideoRepository::class);

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
    }
}
