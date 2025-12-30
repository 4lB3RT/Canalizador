<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\YouTube;

use App\Services\GoogleClientService;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoPublisher;
use Canalizador\Video\Domain\Services\VideoFileValidator;
use Canalizador\Video\Domain\Services\VideoMetadataExtractor;
use Canalizador\Video\Domain\Services\YouTubeServiceFactory;
use Canalizador\Video\Domain\ValueObjects\PublishOptions;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder;
use Canalizador\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;
use Google_Client;

final class YoutubeVideoPublisher implements VideoPublisher
{
    private const int CHUNK_SIZE_BYTES = 1024 * 1024; // 1MB

    public function __construct(
        private readonly GoogleClientService $googleClientService,
        private readonly VideoFileValidator $videoFileValidator,
        private readonly YouTubeVideoBuilder $youtubeVideoBuilder,
        private readonly YouTubeVideoUploader $youtubeVideoUploader,
        private readonly YouTubeServiceFactory $youtubeServiceFactory
    ) {
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function publish(
        Video $video
    ): string {
        $this->videoFileValidator->validate($video);

        $snippet = $this->youtubeVideoBuilder->buildVideoSnippet($video->title()->value(), $video->description()->value(), []);
        $status = $this->youtubeVideoBuilder->buildVideoStatus('private');
        $youtubeVideo = $this->youtubeVideoBuilder->buildVideo($snippet, $status);

        $client = $this->googleClientService->buildYouTubeClient();
        $youtubeService = $this->youtubeServiceFactory->create($client);

        $videoPath = $video->videoLocalPath()->value();

        return $this->youtubeVideoUploader->upload(
            client: $client,
            service: $youtubeService,
            video: $youtubeVideo,
            videoPath: $videoPath,
            chunkSize: self::CHUNK_SIZE_BYTES
        );
    }
}
