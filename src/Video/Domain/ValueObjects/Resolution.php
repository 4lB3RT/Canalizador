<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\ValueObjects;

enum Resolution: string
{
    case HD = '720p';
    case FULL_HD = '1080p';
    case UHD = '4k';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            '720p' => self::HD,
            '1080p' => self::FULL_HD,
            '4k' => self::UHD,
            default => throw new \InvalidArgumentException("Invalid resolution: {$value}"),
        };
    }
}
