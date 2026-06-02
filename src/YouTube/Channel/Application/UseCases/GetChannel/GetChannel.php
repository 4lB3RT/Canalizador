<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\GetChannel;

use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;

final readonly class GetChannel
{
    public function __construct(
        private ChannelRepository $channelRepository,
    ) {
    }

    /**
     * @throws ChannelNotFound
     */
    public function execute(GetChannelRequest $request): Channel
    {
        $channel = $this->channelRepository->findById($request->channelId);

        if (!$channel->userId()->equals($request->userId)) {
            throw ChannelNotFound::withId($request->channelId->value());
        }

        return $channel;
    }
}
