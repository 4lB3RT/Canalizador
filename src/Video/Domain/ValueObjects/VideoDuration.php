<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\ValueObjects;

enum VideoDuration: int
{
    case SHORT = 4;
    case MEDIUM = 6;
    case LONG = 8;

    public static function fromSeconds(int $seconds): self
    {
        return match ($seconds) {
            4 => self::SHORT,
            6 => self::MEDIUM,
            8 => self::LONG,
            default => throw new \InvalidArgumentException("Invalid duration: {$seconds}. Allowed: 4, 6, 8"),
        };
    }

    public static function forReferenceImages(): self
    {
        return self::LONG;
    }
}
