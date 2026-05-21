<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\GetChannel;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;

final readonly class GetChannelRequest
{
    public function __construct(
        public ChannelId $channelId,
        public IntegerId $userId,
    ) {
    }
}
