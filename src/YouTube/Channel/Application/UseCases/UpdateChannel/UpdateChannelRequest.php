<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\UpdateChannel;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;

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
