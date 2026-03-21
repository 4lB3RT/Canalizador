<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\ValueObjects;

enum AspectRatio: string
{
    case LANDSCAPE = '16:9';
    case PORTRAIT = '9:16';

    public static function fromString(string $value): self
    {
        return match ($value) {
            '16:9' => self::LANDSCAPE,
            '9:16' => self::PORTRAIT,
            default => throw new \InvalidArgumentException("Invalid aspect ratio: {$value}"),
        };
    }

    public static function forReferenceImages(): self
    {
        return self::LANDSCAPE;
    }

    public function supportsReferenceImages(): bool
    {
        return $this === self::LANDSCAPE;
    }
}
