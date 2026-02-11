<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;

interface VideoGenerator
{
    public function generate(VideoPrompt $videoPrompt): string;
}
