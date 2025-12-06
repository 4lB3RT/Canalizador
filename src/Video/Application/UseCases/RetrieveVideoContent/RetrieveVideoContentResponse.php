<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\RetrieveVideoContent;

final readonly class RetrieveVideoContentResponse
{
    public function __construct(
        public string $videoPath,
    ) {
    }

    public function toArray(): array
    {
        return [
            'video_path' => $this->videoPath,
        ];
    }
}
