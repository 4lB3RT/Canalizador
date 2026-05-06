<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use App\Services\GoogleClientService;
use Canalizador\Shared\Shared\Domain\ValueObjects\Url;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeServiceFactory;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Repositories\VideoPublisher;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
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
        private readonly ChannelRepository     $channelRepository,
    ) {
    }

    public function publish(Video $video): void
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

        $channel        = $this->channelRepository->findById($video->channelId());
        $client         = $this->googleClientService->buildYouTubeClient($channel->userId()->value());
        $youtubeService = $this->youtubeServiceFactory->create($client);

        $result = $this->youtubeVideoUploader->upload(
            client:    $client,
            service:   $youtubeService,
            video:     $youtubeVideo,
            videoPath: $video->videoLocalPath()->value(),
            chunkSize: self::CHUNK_SIZE_BYTES
        );

        $video->updatePlatformId(PlatformId::fromString($result['id']));
        $video->updateUrl(Url::fromString('https://www.youtube.com/watch?v=' . $result['id']));
    }
}
