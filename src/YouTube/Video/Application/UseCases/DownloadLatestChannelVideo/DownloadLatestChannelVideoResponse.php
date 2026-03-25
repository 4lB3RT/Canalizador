<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo;

final readonly class DownloadLatestChannelVideoResponse
{
    public function __construct(
        public string $youtubeVideoId,
        public string $localPath,
    ) {
    }

    public function toArray(): array
    {
        return [
            'youtube_video_id' => $this->youtubeVideoId,
            'local_path'       => $this->localPath,
        ];
    }
}
