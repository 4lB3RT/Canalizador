<?php

declare(strict_types=1);

namespace Helmreel\Shared\Video\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\Shared\Video\Domain\ValueObjects\VideoMetadata;

interface VideoMetadataGenerator
{
    public function generate(string $scriptContent, Language $language = Language::SPANISH): VideoMetadata;
}
