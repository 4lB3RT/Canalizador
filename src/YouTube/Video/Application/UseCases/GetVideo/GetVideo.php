<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\GetVideo;

use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\ValueObjects\PlatformId;
use Helmreel\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;

final readonly class GetVideo
{
    public function __construct(
        private YouTubeVideoBuilder $videoBuilder,
        private VideoRepository     $videoRepository,
        private ChannelRepository   $channelRepository,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws YouTubeOperationFailed
     * @throws ChannelNotFound
     */
    public function execute(GetVideoRequest $request): GetVideoResponse
    {
        $this->videoBuilder->fromPlatformId(PlatformId::fromString($request->videoId));

        $this->channelRepository->findById($this->videoBuilder->channelId());

        $video = $this->videoBuilder
            ->withDownload()
            ->withAudio()
            ->withTranscription()
            ->build();

        $this->videoRepository->save($video);

        return new GetVideoResponse($video);
    }
}
