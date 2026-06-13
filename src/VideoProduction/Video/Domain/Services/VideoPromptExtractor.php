<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Services;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;

interface VideoPromptExtractor
{
    public function extractWithAvatar(Script $script, Avatar $avatar, VideoCategory $category, Language $voiceLanguage): VideoPrompt;

    public function extract(Script $script, VideoCategory $category, Language $voiceLanguage): VideoPrompt;
}
