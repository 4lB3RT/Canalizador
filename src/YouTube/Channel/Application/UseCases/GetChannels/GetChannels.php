<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\GetChannels;

use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;

final readonly class GetChannels
{
    public function __construct(
        private ChannelRepository $channelRepository,
    ) {
    }

    public function execute(GetChannelsRequest $request): GetChannelsResponse
    {
        $channels = $this->channelRepository->findByUserId($request->userId, $request->pagination);
        $total = $this->channelRepository->countByUserId($request->userId);

        return new GetChannelsResponse(
            channels: $channels,
            total: $total,
            pagination: $request->pagination,
        );
    }
}
