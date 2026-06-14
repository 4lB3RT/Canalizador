<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\ValueObjects;

use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;

enum AvatarMediaType: string
{
    case PROFILE = 'profile';
    case GENERATED = 'generated';
    case FRAME_GAMING = 'frame_gaming';
    case FRAME_METEOROLOGY = 'frame_meteorology';

    public static function fromString(string $value): self
    {
        return self::from(strtolower($value));
    }

    public static function frameForCategory(VideoCategory $category): self
    {
        return match ($category) {
            VideoCategory::GAMING => self::FRAME_GAMING,
            VideoCategory::METEOROLOGY => self::FRAME_METEOROLOGY,
        };
    }
}
