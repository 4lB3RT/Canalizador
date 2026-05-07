<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GetVideo;

use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;

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
