<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Repositories;

use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoMetadata;

interface VideoMetadataGenerator
{
    public function generate(string $scriptContent): VideoMetadata;
}
