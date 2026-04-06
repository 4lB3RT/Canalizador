<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services;

use Canalizador\Shared\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;

final class FfmpegVideoFragmenter implements VideoFragmenter
{
    /**
     * @return LocalPath[]
     * @throws VideoFragmentationFailed
     */
    public function fragment(LocalPath $videoPath, int $segmentDurationSeconds): array
    {
        $fragmentDir = storage_path('app/fragments/' . md5($videoPath->value()));

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
            'ffmpeg -i %s -t %d -c:v libx264 -preset ultrafast -c:a aac -map 0 -segment_time %d -segment_time_delta 0.05 -f segment -reset_timestamps 1 -force_key_frames expr:gte\(t,n_forced*%d\) %s',
            escapeshellarg($videoPath->value()),
            $segmentDurationSeconds,
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
}
