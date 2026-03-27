<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\UpdateChannelWithAI;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelMetadataRepository;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

final readonly class UpdateChannelWithAI
{
    public function __construct(
        private YoutubeChannelRepository $youtubeChannelRepository,
        private ChannelMetadataRepository $channelMetadataRepository,
        private ChannelRepository $channelRepository,
    ) {
    }

    /**
     * @throws ChannelNotFound
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function execute(UpdateChannelWithAIRequest $request): void
    {
        $channelId = ChannelId::fromString($request->channelId);
        $userId = new IntegerId($request->userId);

        $channel = $this->youtubeChannelRepository->findById($channelId);

        if ($channel->userId()->value() === 0) {
            $channel = $this->preserveUserId($channel, $userId);
        }

        $metadata = $this->channelMetadataRepository->generateData($channel);

        $channel->updateTitle($metadata->title());
        $channel->updateDescription($metadata->description());
        $channel->updateCountry($metadata->country());
        $channel->updateChannelBrand($metadata->channelBrand());

        $this->channelRepository->save($channel);
    }

    private function preserveUserId(Channel $channel, IntegerId $userId): Channel
    {
        return new Channel(
            id: $channel->id(),
            userId: $userId,
            title: $channel->title(),
            description: $channel->description(),
            publishedAt: $channel->publishedAt(),
            viewCount: $channel->viewCount(),
            subscriberCount: $channel->subscriberCount(),
            videoCount: $channel->videoCount(),
            privacyStatus: $channel->privacyStatus(),
            country: $channel->country(),
            channelBrand: $channel->channelBrand(),
            customUrl: $channel->customUrl(),
            thumbnailUrl: $channel->thumbnailUrl(),
        );
    }
}

