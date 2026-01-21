<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Video\Application\UseCases\GenerateVideo\ValueObjects\VideoPrompt;

interface VideoGenerator
{
    public function generate(VideoPrompt $videoPrompt, Channel $channel): string;
}
