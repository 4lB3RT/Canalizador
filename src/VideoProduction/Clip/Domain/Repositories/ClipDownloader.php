<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\Repositories;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Url;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;

interface ClipDownloader
{
    /**
     * Polls for completion, downloads the video, and returns the result.
     *
     * @return array{localPath: LocalPath, videoUri: Url} Downloaded file path and Veo video URI
     */
    public function download(GenerationId $generationId, LocalPath $outputPath): array;
}
