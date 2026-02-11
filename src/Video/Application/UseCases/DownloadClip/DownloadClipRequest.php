<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\DownloadClip;

final readonly class DownloadClipRequest
{
    public function __construct(
        public string $clipId,
    ) {
    }
}
