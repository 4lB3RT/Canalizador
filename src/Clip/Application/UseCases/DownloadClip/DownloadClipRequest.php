<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\DownloadClip;

final readonly class DownloadClipRequest
{
    public function __construct(
        public string $clipId,
    ) {
    }
}
