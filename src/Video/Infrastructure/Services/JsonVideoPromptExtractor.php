<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Services;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;

final readonly class JsonVideoPromptExtractor implements VideoPromptExtractor
{
    public function extract(Script $script): string
    {
        $scriptContent = $script->content()->value();
        $jsonData = json_decode($scriptContent, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['video_prompt'])) {
            return $jsonData['video_prompt'];
        }

        return $scriptContent;
    }
}
