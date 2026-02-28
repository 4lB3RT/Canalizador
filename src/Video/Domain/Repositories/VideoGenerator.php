<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\Video\Domain\ValueObjects\Resolution;

interface VideoGenerator
{
    public function generate(VideoPrompt $videoPrompt, ?Resolution $resolution = null): string;
}
