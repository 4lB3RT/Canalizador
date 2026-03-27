<?php

declare(strict_types=1);

namespace Canalizador\Shared\Video\Domain\Repositories;

use Canalizador\Shared\Video\Domain\ValueObjects\VideoMetadata;

interface VideoMetadataGenerator
{
    public function generate(string $scriptContent): VideoMetadata;
}
