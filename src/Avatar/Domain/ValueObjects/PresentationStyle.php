<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Domain\ValueObjects;

enum PresentationStyle: string
{
    case ENERGETIC = 'energetic';
    case CALM = 'calm';
    case PROFESSIONAL = 'professional';
    case CASUAL = 'casual';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'energetic' => self::ENERGETIC,
            'calm' => self::CALM,
            'professional' => self::PROFESSIONAL,
            'casual' => self::CASUAL,
            default => throw new \InvalidArgumentException("Invalid presentation style: {$value}"),
        };
    }
}

