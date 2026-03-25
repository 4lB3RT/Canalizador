<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\FragmentAndPublishVideo;

final readonly class FragmentAndPublishVideoResponse
{
    public function __construct(
        /** @var string[] */
        public array $publishedVideoIds,
    ) {
    }

    public function toArray(): array
    {
        return [
            'published_video_ids' => $this->publishedVideoIds,
        ];
    }
}
