<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;

final class YtDlpVideoDownloader implements VideoDownloader
{
    private const string OUTPUT_DIR = 'youtube_downloads';

    /**
     * @throws YouTubeOperationFailed
     */
    public function download(YouTubeVideoId $videoId): LocalPath
    {
        $outputDir  = storage_path('app/' . self::OUTPUT_DIR);
        $outputPath = $outputDir . '/' . $videoId->value() . '.mp4';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $url = 'https://www.youtube.com/watch?v=' . rawurlencode($videoId->value());

        $cmd = sprintf(
            'yt-dlp -f %s --merge-output-format mp4 -o %s %s',
            escapeshellarg('bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]'),
            escapeshellarg($outputPath),
            escapeshellarg($url)
        );

        $output     = [];
        $resultCode = 0;

        try {
            exec($cmd, $output, $resultCode);
        } catch (\Throwable $e) {
            throw YouTubeOperationFailed::apiError('yt-dlp execution error: ' . $e->getMessage());
        }

        if ($resultCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            throw YouTubeOperationFailed::apiError(
                "yt-dlp failed for video {$videoId->value()}. Exit code: {$resultCode}"
            );
        }

        return new LocalPath($outputPath);
    }
}
