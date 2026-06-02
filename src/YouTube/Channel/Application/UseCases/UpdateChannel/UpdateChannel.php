<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\UpdateChannel;

use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;

final readonly class UpdateChannel
{
    public function __construct(
        private ChannelRepository $channelRepository,
    ) {
    }

    /**
     * @throws ChannelNotFound
     */
    public function execute(UpdateChannelRequest $request): Channel
    {
        $channel = $this->channelRepository->findById($request->channelId);

        if (!$channel->userId()->equals($request->userId)) {
            throw ChannelNotFound::withId($request->channelId->value());
        }

        if ($request->autoSync !== null) {
            $channel->updateAutoSync($request->autoSync);
        }

        if ($request->autoPublish !== null) {
            $channel->updateAutoPublish($request->autoPublish);
        }

        $this->channelRepository->save($channel);

        return $channel;
    }
}
