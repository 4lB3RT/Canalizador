<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\Entities;

use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use DateTimeImmutable;

final readonly class ChannelGoogleToken
{
    public function __construct(
        public ChannelId $channelId,
        public string $accessToken,
        public ?string $refreshToken,
        public ?DateTimeImmutable $expiresAt,
        public ?string $scope,
        public ?string $tokenType,
    ) {
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt <= $now;
    }
}
