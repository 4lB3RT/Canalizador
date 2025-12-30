<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Video\Domain\ValueObjects\VideoMetadata;

interface VideoMetadataGenerator
{
    public function generate(string $scriptContent): VideoMetadata;
}
