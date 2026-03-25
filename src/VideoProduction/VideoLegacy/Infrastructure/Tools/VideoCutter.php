<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Infrastructure\Tools;

use Prism\Prism\Tool;

final class VideoCutter extends Tool
{
    private const string VIDEO_PATH = '/tmp/shorts/';
    private const string CUT_PREFIX = 'yt_video_cut_';

    public function __construct()
    {
        parent::__construct();

        $this->as('VideoCutter')
            ->for('Cut a segment from a video file (with audio) given start and end times, returning the local file path of the cut video segment.')
            ->withStringParameter('videoPath', 'The local file path of the video file.')
            ->withStringParameter('startTime', 'The start time in seconds or HH:MM:SS format.')
            ->withStringParameter('endTime', 'The end time in seconds or HH:MM:SS format.')
            ->using($this);
    }

    public function __invoke(string $videoPath, string $startTime, string $endTime): string
    {
        $outputPath = self::VIDEO_PATH . self::CUT_PREFIX . md5($videoPath . $startTime . $endTime) . '.mp4';

        if (!is_dir(self::VIDEO_PATH)) {
            mkdir(self::VIDEO_PATH, 0777, true);
        }

        $duration = $this->parseTime($endTime) - $this->parseTime($startTime);
        if ($duration <= 0) {
            throw new \InvalidArgumentException('End time must be greater than start time.');
        }

        $cmd = sprintf(
            'ffmpeg -ss %s -i %s -t %s -c copy %s -y',
            escapeshellarg($startTime),
            escapeshellarg($videoPath),
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
