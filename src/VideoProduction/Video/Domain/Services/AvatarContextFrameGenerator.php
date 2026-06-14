<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Services;

use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\AspectRatio;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;

interface AvatarContextFrameGenerator
{
    /**
     * Returns the local path of an image of the avatar placed in the context of the
     * given category (gaming setup / weather studio), ready to be used as the first
     * frame of the video. Generated from the avatar profile image and cached per
     * avatar + category + aspect ratio.
     */
    public function frameFor(Avatar $avatar, VideoCategory $category, AspectRatio $aspectRatio): ?string;
}
