<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\ValueObjects;

enum AvatarMediaType: string
{
    case PROFILE = 'profile';
    case GENERATED = 'generated';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'profile' => self::PROFILE,
            'generated' => self::GENERATED,
            default => throw new \InvalidArgumentException("Invalid avatar media type: {$value}"),
        };
    }
}
