<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\LinkChannel;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;

final readonly class LinkChannelRequest
{
    public function __construct(
        public ChannelId $channelId,
        public IntegerId $userId,
        public string $state,
    ) {
    }
}
