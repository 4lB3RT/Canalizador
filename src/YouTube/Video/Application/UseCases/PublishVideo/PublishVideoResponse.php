<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\PublishVideo;

final readonly class PublishVideoResponse
{
    public function __construct(
        public string $platformVideoId,
        public string $platformUrl,
        public string $platform,
    ) {
    }

    public function toArray(): array
    {
        return [
            'platform_video_id' => $this->platformVideoId,
            'platform_url'      => $this->platformUrl,
            'platform'          => $this->platform,
        ];
    }
}
