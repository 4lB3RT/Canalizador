<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services;

use Canalizador\Shared\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;

final class FfmpegVideoFragmenter implements VideoFragmenter
{
    private const string SHORTS_REFRAME_FILTER = 'crop=ih*9/16:ih,scale=1080:1920,setsar=1';

    /**
     * @return LocalPath[]
     * @throws VideoFragmentationFailed
     */
    public function fragment(LocalPath $videoPath, int $segmentDurationSeconds): array
    {
        $fragmentDir = $this->fragmentCacheDir($videoPath);

        if (is_dir($fragmentDir)) {
            $cached = glob($fragmentDir . '/*.mp4') ?: [];

            if (!empty($cached)) {
                sort($cached);

                return array_map(static fn(string $f) => new LocalPath($f), $cached);
            }
        } else {
            mkdir($fragmentDir, 0755, true);
        }

        $outputPattern = $fragmentDir . '/%03d.mp4';

        $cmd = sprintf(
            'ffmpeg -i %s -t %d -vf %s -c:v libx264 -preset ultrafast -c:a aac -segment_time %d -segment_time_delta 0.05 -f segment -reset_timestamps 1 -force_key_frames expr:gte\(t,n_forced*%d\) %s',
            escapeshellarg($videoPath->value()),
            $segmentDurationSeconds,
            escapeshellarg(self::SHORTS_REFRAME_FILTER),
            $segmentDurationSeconds,
            $segmentDurationSeconds,
            escapeshellarg($outputPattern)
        );

        $output     = [];
        $resultCode = 0;

        try {
            exec($cmd . ' 2>&1', $output, $resultCode);
        } catch (\Throwable $e) {
            throw VideoFragmentationFailed::commandFailed($e->getMessage());
        }

        if ($resultCode !== 0) {
            throw VideoFragmentationFailed::commandFailed(
                "ffmpeg exit code {$resultCode}: " . implode("\n", $output)
            );
        }

        $files = glob($fragmentDir . '/*.mp4');

        if (empty($files)) {
            throw VideoFragmentationFailed::emptyResult($videoPath->value());
        }

        sort($files);

        return array_map(
            static fn (string $file) => new LocalPath($file),
            $files
        );
    }

    public function fragmentAt(LocalPath $videoPath, int $startSeconds, int $durationSeconds): LocalPath
    {
        $fragmentDir = $this->fragmentCacheDir($videoPath);

        if (!is_dir($fragmentDir)) {
            mkdir($fragmentDir, 0755, true);
        }

        $outputPath = $fragmentDir . '/' . sprintf('%03d', (int) ($startSeconds / $durationSeconds)) . '.mp4';

        if (file_exists($outputPath)) {
            return new LocalPath($outputPath);
        }

        $cmd = sprintf(
            'ffmpeg -ss %d -i %s -t %d -vf %s -c:v libx264 -preset ultrafast -c:a aac -reset_timestamps 1 %s',
            $startSeconds,
            escapeshellarg($videoPath->value()),
            $durationSeconds,
            escapeshellarg(self::SHORTS_REFRAME_FILTER),
            escapeshellarg($outputPath)
        );

        $output     = [];
        $resultCode = 0;

        try {
            exec($cmd . ' 2>&1', $output, $resultCode);
        } catch (\Throwable $e) {
            throw VideoFragmentationFailed::commandFailed($e->getMessage());
        }

        if ($resultCode !== 0) {
            throw VideoFragmentationFailed::commandFailed(
                "ffmpeg exit code {$resultCode}: " . implode("\n", $output)
            );
        }

        if (!file_exists($outputPath)) {
            throw VideoFragmentationFailed::emptyResult($videoPath->value());
        }

        return new LocalPath($outputPath);
    }

    private function fragmentCacheDir(LocalPath $videoPath): string
    {
        $cacheKey = md5($videoPath->value() . '|' . self::SHORTS_REFRAME_FILTER);

        return storage_path('app/fragments/' . $cacheKey);
    }
}
