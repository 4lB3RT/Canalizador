<?php

declare(strict_types=1);

namespace Canalizador\Channel\Domain\Entities;

use Canalizador\Shared\Domain\Collection;

final class ChannelCollection extends Collection
{
    protected function type(): string
    {
        return Channel::class;
    }
}

