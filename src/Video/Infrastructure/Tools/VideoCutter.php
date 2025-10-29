<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Tools;

use Prism\Prism\Tool;

final class AudioCutter extends Tool
{
    private const string AUDIO_PATH = '/tmp/audio/';
    private const string CUT_PREFIX = 'yt_audio_cut_';

    public function __construct()
    {
        $this->as('AudioCutter')
            ->for('Cut a segment from an audio file given start and end times, returning the local file path of the cut segment.')
            ->withStringParameter('audioPath', 'The local file path of the audio.')
            ->withStringParameter('startTime', 'The start time in seconds or HH:MM:SS format.')
            ->withStringParameter('endTime', 'The end time in seconds or HH:MM:SS format.')
            ->using($this);
    }

    public function __invoke(string $audioPath, string $startTime, string $endTime): string
    {
        $outputPath = self::AUDIO_PATH . self::CUT_PREFIX . md5($audioPath . $startTime . $endTime) . '.mp3';

        if (!is_dir(self::AUDIO_PATH)) {
            mkdir(self::AUDIO_PATH, 0777, true);
        }

        $duration = $this->parseTime($endTime) - $this->parseTime($startTime);
        if ($duration <= 0) {
            throw new \InvalidArgumentException('End time must be greater than start time.');
        }

        $cmd = sprintf(
            'ffmpeg -i %s -ss %s -t %d -acodec copy %s -y',
            escapeshellarg($audioPath),
            escapeshellarg($startTime),
            $duration,
            escapeshellarg($outputPath)
        );

        $output     = [];
        $resultCode = 0;
        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Audio cutting failed.');
        }

        return $outputPath;
    }

    private function parseTime(string $time): float
    {
        if (preg_match('/^\d+(\.\d+)?$/', $time)) {
            return (float) $time;
        }
        $dt = \DateTime::createFromFormat('H:i:s', $time, new \DateTimeZone('UTC'));
        if ($dt === false) {
            throw new \InvalidArgumentException('Invalid time format.');
        }

        return ($dt->format('H') * 3600) + ($dt->format('i') * 60) + $dt->format('s');
    }
}
