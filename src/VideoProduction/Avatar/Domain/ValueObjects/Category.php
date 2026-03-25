<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\ValueObjects;

enum Category: string
{
    case GAMING = 'gaming';
    case METEOROLOGY = 'meteorology';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'gaming' => self::GAMING,
            'meteorology' => self::METEOROLOGY,
            default => throw new \InvalidArgumentException("Invalid category: {$value}"),
        };
    }
}
