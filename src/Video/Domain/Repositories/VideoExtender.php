<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Shared\Domain\ValueObjects\Url;

interface VideoExtender
{
    /**
     * Extends a Veo-generated video by ~7 seconds using the video URI.
     *
     * @return string The operation name (generation ID) for polling
     */
    public function extend(Url $lastVideoUri): string;
}
