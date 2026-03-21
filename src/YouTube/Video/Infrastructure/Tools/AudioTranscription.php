<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Tools;

use Prism\Prism\Tool;

final class AudioTranscription extends Tool
{
    public function __construct()
    {
        parent::__construct();

        $this->as('AudioTranscription')
            ->for('Transcribe an audio file using OpenAI Whisper and return a structured JSON with segments and words with timestamps.')
            ->withStringParameter('audioPath', 'The local file path of the audio file to transcribe.')
            ->using($this);
    }

    public function __invoke(string $audioPath): string
    {
        if (!is_file($audioPath)) {
            throw new \InvalidArgumentException("Audio file not found: {$audioPath}");
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
                'file'            => new \CURLFile($audioPath),
                'model'           => 'whisper-1',
                'response_format' => 'verbose_json',
                'timestamp_granularities[]' => 'segment',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            throw new \RuntimeException("Whisper transcription failed with HTTP {$httpCode}");
        }

        return $response;
    }
}
