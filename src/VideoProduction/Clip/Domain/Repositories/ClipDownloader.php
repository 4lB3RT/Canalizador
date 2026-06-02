<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Shared\Domain\ValueObjects\Url;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\GenerationId;

interface ClipDownloader
{
    /**
     * Polls for completion, downloads the video, and returns the result.
     *
     * @return array{localPath: LocalPath, videoUri: Url} Downloaded file path and Veo video URI
     */
    public function download(GenerationId $generationId, LocalPath $outputPath): array;
}
