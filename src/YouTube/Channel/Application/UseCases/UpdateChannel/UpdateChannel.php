<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\UpdateChannel;

use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;

final readonly class UpdateChannel
{
    public function __construct(
        private ChannelRepository $channelRepository,
        private YoutubeChannelRepository $youtubeChannelRepository,
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

        $metadataChanged = false;

        if ($request->title !== null) {
            $channel->updateTitle(Title::fromString($request->title));
            $metadataChanged = true;
        }

        if ($request->description !== null) {
            $channel->updateDescription(Description::fromString($request->description));
            $metadataChanged = true;
        }

        $this->channelRepository->save($channel);

        if ($metadataChanged) {
            $this->youtubeChannelRepository->save($channel);
        }

        return $channel;
    }
}
