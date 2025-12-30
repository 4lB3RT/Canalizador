<?php

declare(strict_types=1);

namespace Canalizador\Channel\Application\UseCases\SyncChannel;

use Canalizador\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

final readonly class SyncChannel
{
    public function __construct(
        private ChannelRepository $channelRepository,
        private YoutubeChannelRepository $youtubeChannelRepository,
    ) {
    }

    /**
     * @throws ChannelNotFound
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function execute(SyncChannelRequest $request): void
    {
        $channelId = ChannelId::fromString($request->channelId);

        $channel = $this->channelRepository->findById($channelId);

        $this->youtubeChannelRepository->save($channel);
    }
}

