<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Tools;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Prism\Prism\Tool;

final class AudioExtractor extends Tool
{
    private const string AUDIO_FORMAT       = 'mp3';
    private const string AUDIO_PATH         = '/tmp/audios/';
    private const string CACHE_AUDIO_PREFIX = 'yt_audio_preview';

    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {
        parent::__construct();

        $this->as('AudioExtractor')
            ->for('Extract the audio from a video file and return the local file path of the extracted audio.')
            ->withStringParameter('videoId', 'The unique identifier of the video.')
            ->using($this);
    }

    public function __invoke(string $videoId): void
    {
        $videoId = VideoId::fromString($videoId);
        $video   = $this->videoRepository->findById(videoId: $videoId);

        if ($video && $video->audioLocalPath() !== null) {
            return;
        }

        $audioLocalPath = LocalPath::fromString($this->extractAudio($video));

        $video->updateAudioLocalPath($audioLocalPath);

        $this->videoRepository->save($video);
    }

    private function extractAudio(Video $video): string
    {
        if (!is_dir(self::AUDIO_PATH)) {
            mkdir(self::AUDIO_PATH, 0777, true);
        }

        $cacheKey   = md5($video->id()->value());
        $outputPath = self::AUDIO_PATH . self::CACHE_AUDIO_PREFIX . $cacheKey . '_preview.' . self::AUDIO_FORMAT;

        $cmd = sprintf(
            'ffmpeg -i %s -vn -acodec libmp3lame -ar 44100 -ac 2 -ab 192k -f mp3 %s -y',
            escapeshellarg($video->videoLocalPath()->value()),
            escapeshellarg($outputPath)
        );

        try {
            $output     = [];
            $resultCode = 0;
            exec($cmd, $output, $resultCode);
        } catch (\Throwable $e) {
            \Log::error('Error extracting audio: ' . $e->getMessage(), [
                'videoId' => $videoId,
                'trace'   => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to extract audio for video ID: ' . $videoId);
        }

        if ($resultCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            \Log::error('Audio extraction failed or file is invalid', [
                'videoPath'  => $video->videoLocalPath()->value(),
                'outputPath' => $outputPath,
                'output'     => $output,
                'resultCode' => $resultCode,
            ]);
            throw new \RuntimeException('Audio extraction failed for video ID: ' . $videoId);
        }

        return $outputPath;
    }
}
