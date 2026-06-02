<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo;

final readonly class SmartFragmentAndPublishVideoResponse
{
    /**
     * @param string[] $publishedVideoIds
     */
    public function __construct(
        public array $publishedVideoIds,
    ) {
    }

    public function toArray(): array
    {
        return [
            'publishedVideoIds' => $this->publishedVideoIds,
        ];
    }
}
