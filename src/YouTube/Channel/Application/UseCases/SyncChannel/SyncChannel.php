<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\SyncChannel;

use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use Google\Service\Exception;
use Google_Service_Exception;

final readonly class SyncChannel
{
    public function __construct(
        private ChannelRepository $channelRepository,
        private YoutubeChannelRepository $youtubeChannelRepository,
    ) {
    }

    /**
     * @throws ChannelNotFound
     * @throws Exception
     * @throws Google_Service_Exception
     */
    public function execute(SyncChannelRequest $request): void
    {
        $channelId = ChannelId::fromString($request->channelId);

        $channel = $this->channelRepository->findById($channelId);

        $this->youtubeChannelRepository->save($channel);
    }
}

