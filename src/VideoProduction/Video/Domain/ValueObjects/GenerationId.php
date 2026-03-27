<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\ValueObjects;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\StringValue;

final readonly class GenerationId extends StringValue
{
    private const string PENDING = 'pending';

    public static function pending(): static
    {
        return new static(self::PENDING);
    }

    public function isPending(): bool
    {
        return $this->value() === self::PENDING;
    }
}
