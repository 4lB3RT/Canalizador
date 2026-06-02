<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\UpdateChannel;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;

final readonly class UpdateChannelRequest
{
    public function __construct(
        public ChannelId $channelId,
        public IntegerId $userId,
        public ?bool $autoSync = null,
        public ?bool $autoPublish = null,
    ) {
    }
}
