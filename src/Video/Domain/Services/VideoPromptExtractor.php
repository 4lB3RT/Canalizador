<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Services;

use Canalizador\Script\Domain\Entities\Script;

interface VideoPromptExtractor
{
    public function extract(Script $script): string;
}
