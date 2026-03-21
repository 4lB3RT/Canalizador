<?php

declare(strict_types=1);

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
                'file'                      => new \CURLFile($audioPath->value()),
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
