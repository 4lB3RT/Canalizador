<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\GetChannel;

use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;

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
