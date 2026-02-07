<?php

declare(strict_types=1);

namespace Canalizador\Shared\Domain\ValueObjects;

enum ImageMimeType: string
{
    case JPEG = 'image/jpeg';
    case PNG = 'image/png';
    case GIF = 'image/gif';
    case WEBP = 'image/webp';

    public static function fromExtension(string $extension): self
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg' => self::JPEG,
            'png' => self::PNG,
            'gif' => self::GIF,
            'webp' => self::WEBP,
            default => self::PNG,
        };
    }

    public static function fromFilePath(string $filePath): self
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return self::fromExtension($extension);
    }
}
