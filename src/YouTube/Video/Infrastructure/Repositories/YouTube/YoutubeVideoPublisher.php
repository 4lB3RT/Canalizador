<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use App\Services\GoogleClientService;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeServiceFactory;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Repositories\VideoPublisher;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoBuilder;
use Canalizador\YouTube\Video\Infrastructure\Services\YouTube\YouTubeVideoUploader;

final class YoutubeVideoPublisher implements VideoPublisher
{
    private const int CHUNK_SIZE_BYTES = 1024 * 1024;

    public function __construct(
        private readonly GoogleClientService   $googleClientService,
        private readonly YouTubeVideoBuilder   $youtubeVideoBuilder,
        private readonly YouTubeVideoUploader  $youtubeVideoUploader,
        private readonly YouTubeServiceFactory $youtubeServiceFactory,
    ) {
    }

    public function publish(Video $video): string
    {
        $snippet = $this->youtubeVideoBuilder->buildVideoSnippet(
            $video->title()->value(),
            $video->description()?->value() ?? '',
            []
        );

        $privacyStatus = $video->status() === YouTubeStatus::Scheduled
            ? YouTubeStatus::Private->value
            : $video->status()->value;

        $publishAt = $video->status() === YouTubeStatus::Scheduled
            ? $video->publishedAt()->value()
            : null;

        $status       = $this->youtubeVideoBuilder->buildVideoStatus($privacyStatus, $publishAt);
        $youtubeVideo = $this->youtubeVideoBuilder->buildVideo($snippet, $status);

        $client         = $this->googleClientService->buildYouTubeClient();
        $youtubeService = $this->youtubeServiceFactory->create($client);

        return $this->youtubeVideoUploader->upload(
            client:    $client,
            service:   $youtubeService,
            video:     $youtubeVideo,
            videoPath: $video->videoLocalPath()->value(),
            chunkSize: self::CHUNK_SIZE_BYTES
        );
    }
}
