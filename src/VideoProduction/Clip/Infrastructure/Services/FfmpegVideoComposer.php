<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Infrastructure\Services;

use Helmreel\VideoProduction\Clip\Domain\Services\VideoComposer;

final class FfmpegVideoComposer implements VideoComposer
{
    public function extractAudio(string $videoPath, string $outputAudioPath): void
    {
        $cmd = sprintf(
            'ffmpeg -i %s -vn -acodec libmp3lame -ar 44100 -ac 2 -ab 192k %s -y',
            escapeshellarg($videoPath),
            escapeshellarg($outputAudioPath)
        );

        $this->execute($cmd, $outputAudioPath);
    }

    public function replaceAudio(string $videoPath, string $audioPath, string $outputPath): void
    {
        $cmd = sprintf(
            'ffmpeg -i %s -i %s -c:v copy -c:a aac -map 0:v:0 -map 1:a:0 -shortest %s -y',
            escapeshellarg($videoPath),
            escapeshellarg($audioPath),
            escapeshellarg($outputPath)
        );

        $this->execute($cmd, $outputPath);
    }

    public function subtractAudio(string $originalPath, string $toSubtractPath, string $outputPath): void
    {
        $cmd = sprintf(
            'ffmpeg -i %s -i %s -filter_complex '
            . '"[0:a][1:a]sidechaincompress=threshold=0.01:ratio=20:attack=5:release=300:knee=8[sc];'
            . '[sc]afftfilt=real=\'if(between(b*sr/nb,300,2500),re*0.25,re)\':imag=\'if(between(b*sr/nb,300,2500),im*0.25,im)\':win_size=4096:overlap=0.75[out]" '
            . '-map "[out]" %s -y',
            escapeshellarg($originalPath),
            escapeshellarg($toSubtractPath),
            escapeshellarg($outputPath)
        );

        $this->execute($cmd, $outputPath);
    }

    public function mixAudio(string $audioPath1, string $audioPath2, string $outputPath): void
    {
        $cmd = sprintf(
            'ffmpeg -i %s -i %s -filter_complex "[0:a]volume=0.8[voice];[1:a]volume=0.7[bg];[voice][bg]amix=inputs=2:duration=first:normalize=0[out]" -map "[out]" %s -y',
            escapeshellarg($audioPath1),
            escapeshellarg($audioPath2),
            escapeshellarg($outputPath)
        );

        $this->execute($cmd, $outputPath);
    }

    public function extractLastFrame(string $videoPath, string $outputImagePath): void
    {
        $cmd = sprintf(
            'ffmpeg -sseof -1 -i %s -update 1 -frames:v 1 -q:v 2 %s -y',
            escapeshellarg($videoPath),
            escapeshellarg($outputImagePath)
        );

        $this->execute($cmd, $outputImagePath);
    }

    public function concatenate(array $videoPaths, string $outputPath): void
    {
        if (count($videoPaths) === 1) {
            copy($videoPaths[0], $outputPath);

            return;
        }

        $listFile = tempnam(sys_get_temp_dir(), 'concat_') . '.txt';
        $lines = array_map(
            static fn (string $p) => "file '" . str_replace("'", "'\\''", $p) . "'",
            $videoPaths,
        );
        file_put_contents($listFile, implode("\n", $lines) . "\n");

        $cmd = sprintf(
            'ffmpeg -f concat -safe 0 -i %s -c:v libx264 -c:a aac -vsync vfr %s -y',
            escapeshellarg($listFile),
            escapeshellarg($outputPath)
        );

        try {
            $this->execute($cmd, $outputPath);
        } finally {
            @unlink($listFile);
        }
    }

    private function execute(string $cmd, string $expectedOutput): void
    {
        $output     = [];
        $resultCode = 0;
        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0 || !is_file($expectedOutput) || filesize($expectedOutput) === 0) {
            \Log::error('FFmpeg command failed', [
                'cmd'        => $cmd,
                'output'     => $output,
                'resultCode' => $resultCode,
            ]);
            throw new \RuntimeException("FFmpeg command failed with code {$resultCode}");
        }
    }
}
