<?php

declare(strict_types=1);

namespace Canalizador\Channel\Domain\Exceptions;

use Canalizador\Shared\Domain\Exceptions\EntityNotFound;

final class ChannelNotFound extends EntityNotFound
{
    public static function withId(string $channelId): self
    {
        return new self("Channel not found with ID: {$channelId}");
    }
}

