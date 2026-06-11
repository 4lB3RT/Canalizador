<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Domain\ValueObjects;

enum ImageType: string
{
    case PROFILE = 'profile';
    case GENERATED = 'generated';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'profile' => self::PROFILE,
            'generated' => self::GENERATED,
            default => throw new \InvalidArgumentException("Invalid image type: {$value}"),
        };
    }
}
