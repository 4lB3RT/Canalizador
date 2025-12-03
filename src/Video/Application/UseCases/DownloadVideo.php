<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\UseCases;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Minutes;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class DownloadVideo
{
    private const string VIDEO_FORMAT       = 'mp4';
    private const string VIDEO_PATH         = '/tmp/videos/';
    private const string CACHE_VIDEO_PREFIX = 'yt_video_preview';

    public function __construct(
        private VideoRepository $videoRepository,
        private VideoRepository $externalVideoRepository,
    ) {
    }

    public function execute(VideoId $videoId, Minutes $minutes): ?Video
    {
        $video = $this->videoRepository->findById(videoId: $videoId);

        if ($video === null) {
            $video = $this->externalVideoRepository->findById(videoId: $videoId);
        }

        if ($video && $video->videoLocalPath() !== null) {
            return $video;
        }


        $videoLocalPath = $this->downloadVideo($videoId, $minutes);

        $video->updateVideoLocalPath($videoLocalPath);

        $this->videoRepository->save($video);

        return $video;
    }

    private function downloadVideo(VideoId $videoId, Minutes $minutes): LocalPath
    {
        $cacheKey   = md5($videoId->value() . "_{$minutes->value()}min");
        $outputPath = self::VIDEO_PATH . self::CACHE_VIDEO_PREFIX . $cacheKey . '_preview.' . self::VIDEO_FORMAT;

        $duration    = max(1, $minutes->value()) * 60;
        $mm          = (int) floor($duration / 60);
        $ss          = ($duration % 60);
        $durationStr = sprintf('%02d:%02d', $mm, $ss);

        $url = 'https://www.youtube.com/watch?v=' . rawurlencode($videoId->value());

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
                'videoId' => $videoId->value(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to download video preview for video ID: ' . $videoId->value());
        }

        if ($resultCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Video preview download failed or file is invalid for video ID: ' . $videoId->value());
        }

        return new LocalPath($outputPath);
    }
}
