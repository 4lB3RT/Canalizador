<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Application\UseCases\SyncLastVideo;

final readonly class SyncLastVideoRequest
{
    public function __construct(
        public string $channelId,
    ) {
    }
}
