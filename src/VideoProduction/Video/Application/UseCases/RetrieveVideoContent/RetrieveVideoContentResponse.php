<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\RetrieveVideoContent;

use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;

final readonly class RetrieveVideoContentResponse
{
    public function __construct(
        public LocalPath $videoPath,
    ) {
    }

    public function toArray(): array
    {
        return [
            'video_path' => $this->videoPath->value(),
        ];
    }
}
