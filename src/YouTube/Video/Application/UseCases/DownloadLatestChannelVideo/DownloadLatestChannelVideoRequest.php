<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo;

final readonly class DownloadLatestChannelVideoRequest
{
    public function __construct(
        public string $channelId
    ) {
    }
}
