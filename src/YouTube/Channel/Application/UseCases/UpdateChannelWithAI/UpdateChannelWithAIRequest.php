<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\UpdateChannelWithAI;

final readonly class UpdateChannelWithAIRequest
{
    public function __construct(
        public string $channelId,
        public int $userId,
    ) {
    }
}

