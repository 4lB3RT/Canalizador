<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Services;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;

final readonly class JsonVideoPromptExtractor implements VideoPromptExtractor
{
    public function extract(Script $script): string
    {
        return $script->content()->value();
    }
}
