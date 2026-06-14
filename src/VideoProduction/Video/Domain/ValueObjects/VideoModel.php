<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\ValueObjects;

enum VideoModel: string
{
    case VEO_31_LITE = 'veo-3.1-lite-generate-preview';
    case VEO_31_FAST = 'veo-3.1-fast-generate-preview';
    case VEO_30_FAST = 'veo-3.0-fast-generate-001';
    case VEO_31 = 'veo-3.1-generate-preview';
    case VEO_30 = 'veo-3.0-generate-001';

    /**
     * @return Resolution[]
     */
    public function supportedResolutions(): array
    {
        return match ($this) {
            self::VEO_31_LITE => [Resolution::HD, Resolution::FULL_HD],
            default => [Resolution::HD, Resolution::FULL_HD, Resolution::UHD],
        };
    }

    public function supportsResolution(Resolution $resolution): bool
    {
        return in_array($resolution, $this->supportedResolutions(), true);
    }

    public function supportsReferenceImages(): bool
    {
        return match ($this) {
            self::VEO_31, self::VEO_31_FAST => true,
            default => false,
        };
    }
}
