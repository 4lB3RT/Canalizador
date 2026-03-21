<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use App\Services\GoogleClientService;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoPublisher;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeServiceFactory;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;

final class YoutubeVideoPublisher implements VideoPublisher
{
    private const int CHUNK_SIZE_BYTES = 1024 * 1024; // 1MB

    public function __construct(
        private readonly GoogleClientService $googleClientService,
        private readonly YouTubeVideoBuilder $youtubeVideoBuilder,
        private readonly YouTubeVideoUploader $youtubeVideoUploader,
        private readonly YouTubeServiceFactory $youtubeServiceFactory
    ) {
    }

    /**
     * @throws YouTubeOperationFailed
     */
    public function publish(VideoToPublish $video): string
    {
        $snippet      = $this->youtubeVideoBuilder->buildVideoSnippet($video->title, $video->description, []);
        $status       = $this->youtubeVideoBuilder->buildVideoStatus('private');
        $youtubeVideo = $this->youtubeVideoBuilder->buildVideo($snippet, $status);

        $client         = $this->googleClientService->buildYouTubeClient();
        $youtubeService = $this->youtubeServiceFactory->create($client);

        return $this->youtubeVideoUploader->upload(
            client:    $client,
            service:   $youtubeService,
            video:     $youtubeVideo,
            videoPath: $video->localPath->value(),
            chunkSize: self::CHUNK_SIZE_BYTES
        );
    }
}
