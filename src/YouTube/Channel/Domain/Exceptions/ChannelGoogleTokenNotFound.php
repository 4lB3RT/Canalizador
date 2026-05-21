<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\Exceptions;

use Canalizador\Shared\Shared\Domain\Exceptions\EntityNotFound;

final class ChannelGoogleTokenNotFound extends EntityNotFound
{
    public static function forChannelId(string $channelId): self
    {
        return new self("No Google token linked to channel: {$channelId}");
    }
}
