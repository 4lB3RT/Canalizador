<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\RegisterChannel;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;

final readonly class RegisterChannel
{
    public function __construct(
        private ChannelRepository $externalChannelRepository,
        private ChannelRepository $internalChannelRepository,
    ) {
    }

    /**
     * @throws ChannelNotFound
     */
    public function execute(RegisterChannelRequest $request): void
    {
        $channelId = ChannelId::fromString($request->channelId);

        $channel = $this->externalChannelRepository->findById($channelId);
        $channel->updateUserId(new IntegerId($request->userId));

        $this->internalChannelRepository->save($channel);
    }
}
