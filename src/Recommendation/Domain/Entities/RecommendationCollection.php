<?php

declare(strict_types = 1);

namespace Canalizador\Recommendation\Domain\Entities;

use Canalizador\Shared\Domain\Collection;

final class RecommendationCollection extends Collection
{
    protected function type(): string
    {
        return Recommendation::class;
    }
}
