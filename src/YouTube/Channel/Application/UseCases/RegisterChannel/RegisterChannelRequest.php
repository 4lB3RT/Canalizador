<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\RegisterChannel;

final readonly class RegisterChannelRequest
{
    public function __construct(
        public string $channelId,
        public int    $userId,
    ) {
    }
}
