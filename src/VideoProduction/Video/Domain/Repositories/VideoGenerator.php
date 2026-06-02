<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Repositories;

use Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\Resolution;

interface VideoGenerator
{
    public function generate(VideoPrompt $videoPrompt, ?Resolution $resolution = null): string;
}
