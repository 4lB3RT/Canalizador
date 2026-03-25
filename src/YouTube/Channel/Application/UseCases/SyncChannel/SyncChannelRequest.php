<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\SyncChannel;

final readonly class SyncChannelRequest
{
    public function __construct(
        public string $channelId,
    ) {
    }
}

