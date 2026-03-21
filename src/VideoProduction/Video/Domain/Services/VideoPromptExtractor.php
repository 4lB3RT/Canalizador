<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Services;

use Canalizador\VideoProduction\Avatar\Domain\Entities\Avatar;
use Canalizador\VideoProduction\Script\Domain\Entities\Script;
use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoCategory;

interface VideoPromptExtractor
{
    public function extractWithAvatar(Script $script, Avatar $avatar, VideoCategory $category): VideoPrompt;
    
    public function extract(Script $script, VideoCategory $category): VideoPrompt;
}
