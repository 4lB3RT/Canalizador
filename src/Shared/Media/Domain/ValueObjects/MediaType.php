<?php

declare(strict_types=1);

namespace Helmreel\Shared\Media\Domain\ValueObjects;

enum MediaType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'image' => self::IMAGE,
            'video' => self::VIDEO,
            default => throw new \InvalidArgumentException("Invalid media type: {$value}"),
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::IMAGE => 'image/png',
            self::VIDEO => 'video/mp4',
        };
    }
}
