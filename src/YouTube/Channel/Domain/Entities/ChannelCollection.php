<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Domain\Entities;

use Helmreel\Shared\Shared\Domain\Collection;

final class ChannelCollection extends Collection
{
    protected function type(): string
    {
        return Channel::class;
    }
}

