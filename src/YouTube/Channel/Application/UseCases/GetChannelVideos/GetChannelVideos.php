<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\GetChannelVideos;

use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;

final readonly class GetChannelVideos
{
    public function __construct(
        private ChannelRepository $channelRepository,
        private VideoRepository $videoRepository,
    ) {
    }

    /**
     * @throws ChannelNotFound
     */
    public function execute(GetChannelVideosRequest $request): GetChannelVideosResponse
    {
        $channel = $this->channelRepository->findById($request->channelId);

        if (!$channel->userId()->equals($request->userId)) {
            throw ChannelNotFound::withId($request->channelId->value());
        }

        $videos = $this->videoRepository->findByChannelId(
            $request->channelId,
            $request->category,
            $request->pagination,
        );
        $total = $this->videoRepository->countByChannelId(
            $request->channelId,
            $request->category,
        );

        return new GetChannelVideosResponse(
            videos:     $videos,
            total:      $total,
            pagination: $request->pagination,
        );
    }
}
