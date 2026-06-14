<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Services;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\AspectRatio;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoModel;

interface VideoPromptExtractor
{
    public function extractWithAvatar(Script $script, Avatar $avatar, VideoCategory $category, Language $voiceLanguage, VideoModel $model, AspectRatio $aspectRatio): VideoPrompt;

    public function extract(Script $script, VideoCategory $category, Language $voiceLanguage): VideoPrompt;
}
