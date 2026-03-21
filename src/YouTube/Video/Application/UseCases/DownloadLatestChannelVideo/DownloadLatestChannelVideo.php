<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo;

use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\ChannelVideoFinder;
use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader;

final readonly class DownloadLatestChannelVideo
{
    public function __construct(
        private ChannelVideoFinder $channelVideoFinder,
        private VideoDownloader $videoDownloader,
    ) {
    }

    /**
     * @throws YouTubeOperationFailed
     */
    public function execute(DownloadLatestChannelVideoRequest $request): DownloadLatestChannelVideoResponse
    {
        $youtubeVideoId = $this->channelVideoFinder->findLatestByChannelId($request->channelId);
        $localPath      = $this->videoDownloader->download($youtubeVideoId);

        return new DownloadLatestChannelVideoResponse(
            youtubeVideoId: $youtubeVideoId->value(),
            localPath:      $localPath->value(),
        );
    }
}
