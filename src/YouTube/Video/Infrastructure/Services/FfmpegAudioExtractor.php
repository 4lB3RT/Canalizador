<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;

final class FfmpegAudioExtractor implements AudioExtractor
{
    private const string AUDIO_DIR    = 'app/audio/';
    private const string AUDIO_PREFIX = 'audio_';

    public function extract(LocalPath $videoPath): AudioPath
    {
        $audioDir = storage_path(self::AUDIO_DIR);

        if (!is_dir($audioDir)) {
            mkdir($audioDir, 0755, true);
        }

        $outputPath = $audioDir . self::AUDIO_PREFIX . md5($videoPath->value()) . '.mp3';

        $cmd = sprintf(
            'ffmpeg -i %s -vn -acodec libmp3lame -ar 44100 -ac 2 -ab 192k -f mp3 %s -y 2>&1',
            escapeshellarg($videoPath->value()),
            escapeshellarg($outputPath)
        );

        $output     = [];
        $resultCode = 0;
        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException(
                'Audio extraction failed for: ' . $videoPath->value() . "\n" . implode("\n", $output)
            );
        }

        return AudioPath::fromString($outputPath);
    }
}
