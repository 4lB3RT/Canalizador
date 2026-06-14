<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\PublishProductionVideo;

final readonly class PublishProductionVideoResponse
{
    public function __construct(
        public string $platformVideoId,
        public string $platformUrl,
        public string $privacy,
    ) {
    }

    public function toArray(): array
    {
        return [
            'platform_video_id' => $this->platformVideoId,
            'platform_url'      => $this->platformUrl,
            'privacy'           => $this->privacy,
        ];
    }
}
