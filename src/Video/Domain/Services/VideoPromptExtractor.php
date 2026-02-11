<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Services;

use Canalizador\Avatar\Domain\Entities\Avatar;
use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;

interface VideoPromptExtractor
{
    public function extractWithAvatar(Script $script, Avatar $avatar, VideoCategory $category): VideoPrompt;
    
    public function extract(Script $script, VideoCategory $category): VideoPrompt;
}
