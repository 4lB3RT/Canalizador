<?php

declare(strict_types = 1);

namespace Canalizador\VideoLegacy\Infrastructure\Tools;

use Canalizador\Shared\Domain\ValueObjects\Minutes;
use Canalizador\VideoLegacy\Application\UseCases\DownloadVideo;
use Canalizador\VideoLegacy\Domain\ValueObjects\VideoId;
use Prism\Prism\Tool;

final class VideoDownloader extends Tool
{
    public function __construct(
        private readonly DownloadVideo $downloadVideo,
    ) {
        parent::__construct();

        $this->as('VideoDownloader')
            ->for('Download a YouTube video given its ID and return the local file path of the downloaded video.')
            ->withStringParameter('videoId', 'The YouTube video ID.')
            ->withNumberParameter('minutes', 'The number of minutes to download from the start of the video.')
            ->using($this);
    }

    public function __invoke(string $videoId, int $minutes = 3): String
    {
        $videoId = VideoId::fromString($videoId);
        $minutes = Minutes::fromInt($minutes);

        $video = $this->downloadVideo->execute($videoId, $minutes);

        return $video->id()->value();
    }
}
