<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\PublishProductionVideo;

final readonly class PublishProductionVideoRequest
{
    public function __construct(
        public string $videoId,
        public string $channelId,
        public string $privacy,
        public string $title,
        public string $description,
        public string $mediaId,
        public ?string $publishAt = null,
    ) {
    }
}
