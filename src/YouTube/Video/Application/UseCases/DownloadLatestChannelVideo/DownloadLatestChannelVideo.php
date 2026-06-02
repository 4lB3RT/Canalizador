<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo;

use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\Repositories\ChannelVideoFinder;
use Helmreel\YouTube\Video\Domain\Repositories\VideoDownloader;

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
