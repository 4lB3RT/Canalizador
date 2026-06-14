<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Url;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\AspectRatio;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoModel;

interface VideoExtender
{
    /**
     * Extends a Veo-generated video by ~7 seconds using the video URI.
     *
     * @return string The operation name (generation ID) for polling
     */
    public function extend(Url $lastVideoUri, string $clipPrompt, ?VideoModel $model = null, ?AspectRatio $aspectRatio = null): string;
}
