<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\RegisterChannel;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;

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
