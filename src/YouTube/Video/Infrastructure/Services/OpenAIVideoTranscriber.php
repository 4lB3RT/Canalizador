<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Services;

use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;

final class OpenAIVideoTranscriber implements VideoTranscriber
{
    /**
     * @return array<int, array{start: float, end: float, text: string}>
     */
    public function transcribe(AudioPath $audioPath): array
    {
        if (!is_file($audioPath->value())) {
            throw new \InvalidArgumentException("Audio file not found: {$audioPath->value()}");
        }

        $whisperReadyPath = $this->compressForWhisper($audioPath->value());

        try {
            return $this->callWhisper($whisperReadyPath);
        } finally {
            @unlink($whisperReadyPath);
        }
    }

    private function compressForWhisper(string $sourcePath): string
    {
        $compressedPath = tempnam(sys_get_temp_dir(), 'whisper_') . '.mp3';

        $cmd = sprintf(
            'ffmpeg -i %s -vn -acodec libmp3lame -ar 16000 -ac 1 -ab 64k -f mp3 %s -y 2>&1',
            escapeshellarg($sourcePath),
            escapeshellarg($compressedPath)
        );

        $output     = [];
        $resultCode = 0;
        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0 || !is_file($compressedPath) || filesize($compressedPath) === 0) {
            @unlink($compressedPath);
            throw new \RuntimeException(
                'Whisper audio compression failed for: ' . $sourcePath . "\n" . implode("\n", $output)
            );
        }

        return $compressedPath;
    }

    /**
     * @return array<int, array{start: float, end: float, text: string}>
     */
    private function callWhisper(string $audioPath): array
    {
        $apiKey = config('services.openai.key');
        $url    = 'https://api.openai.com/v1/audio/transcriptions';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => [
                'file'                      => new \CURLFile($audioPath),
                'model'                     => 'whisper-1',
                'response_format'           => 'verbose_json',
                'timestamp_granularities[]' => 'segment',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            throw new \RuntimeException("Whisper transcription failed with HTTP {$httpCode}");
        }

        $data = json_decode($response, true);

        if (!isset($data['segments']) || !is_array($data['segments'])) {
            throw new \RuntimeException('Unexpected Whisper response structure — missing segments');
        }

        return array_map(
            static fn (array $segment) => [
                'start' => (float) $segment['start'],
                'end'   => (float) $segment['end'],
                'text'  => (string) $segment['text'],
            ],
            $data['segments']
        );
    }
}
