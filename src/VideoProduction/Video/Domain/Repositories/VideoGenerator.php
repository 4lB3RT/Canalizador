<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Repositories;

use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Resolution;

interface VideoGenerator
{
    public function generate(VideoPrompt $videoPrompt, ?Resolution $resolution = null): string;
}
