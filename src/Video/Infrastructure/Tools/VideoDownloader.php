<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Tools;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\Category;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Prism\Prism\Tool;

final class VideoDownloader extends Tool
{
    private const string VIDEO_FORMAT = 'mp4';

    private const string VIDEO_PATH         = '/tmp/videos/';
    private const string CACHE_VIDEO_PREFIX = 'yt_video_preview';

    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {
        parent::__construct();

        $this->as('VideoDownloader')
            ->for('Download a YouTube video given its ID and return the local file path of the downloaded video.')
            ->withStringParameter('videoId', 'The YouTube video ID.')
            ->withNumberParameter('minutes', 'The number of minutes to download from the start of the video.')
            ->using($this);
    }

    public function __invoke(string $videoId, int $minutes = 3): void
    {
        $videoId = VideoId::fromString($videoId);
        $video   = $this->videoRepository->findById(videoId: $videoId);

        if ($video && $video->videoLocalPath() !== null) {
            return;
        }

        $videoLocalPath = $this->downloadVideo($videoId->value(), $minutes);

        $video = new Video(
            id: $videoId,
            title: Title::fromString('test'),
            publishedAt: new DateTime(\DateTimeImmutable::createFromFormat('Y-m-d', '2025-10-29')),
            metrics: MetricCollection::empty(),
            category: Category::fromString('Youtube'),
            videoLocalPath: $videoLocalPath,
        );

        $this->videoRepository->save($video);
    }

    private function downloadVideo(string $videoId, int $minutes = 3): LocalPath
    {
        $cacheKey   = md5($videoId . "_{$minutes}min");
        $outputPath = self::VIDEO_PATH . self::CACHE_VIDEO_PREFIX . $cacheKey . '_preview.' . self::VIDEO_FORMAT;

        $duration    = max(1, $minutes) * 60;
        $mm          = (int) floor($duration / 60);
        $ss          = ($duration % 60);
        $durationStr = sprintf('%02d:%02d', $mm, $ss);

        $url = 'https://www.youtube.com/watch?v=' . rawurlencode($videoId);

        $cmd = sprintf(
            'yt-dlp -f mp4 --download-sections %s -o %s %s',
            escapeshellarg("*00:00-{$durationStr}"),
            escapeshellarg($outputPath),
            escapeshellarg($url)
        );

        try {
            $output     = [];
            $resultCode = 0;
            exec($cmd, $output, $resultCode);
        } catch (\Throwable $e) {
            \Log::error('Error downloading video preview: ' . $e->getMessage(), [
                'videoId' => $videoId,
                'trace'   => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to download video preview for video ID: ' . $videoId);
        }

        if ($resultCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Video preview download failed or file is invalid for video ID: ' . $videoId);
        }

        return new LocalPath($outputPath);
    }
}
