<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\Service;

use Canalizador\Video\Domain\ValueObjects\VideoId;
use OpenAI\Client as OpenAIClient;

class VideoTranscriptionService
{
    private const string WHISPER_MODEL  = 'whisper-1';
    private const string AUDIO_FORMAT   = 'mp3';
    private const string AUDIO_LANGUAGE = 'es';
    private const string AUDIO_PATH     = '/tmp/';

    private const string JSON_PATH = '/tmp/';
    private const MODEL_NAME       = 'gpt-4o-mini';

    protected OpenAIClient $client;

    public function __construct()
    {
        $this->client = \OpenAI::client(config('services.openai.key'));
    }

    public function getRelevantSegmentsByTime(VideoId $videoId): array
    {
        $segments         = $this->transcribeWithWhisper($this->downloadAudioPreviewFromYoutube($videoId, 3));
        $minSeconds       = 20;
        $maxSeconds       = 60;
        $relevantSegments = [];
        $skipUntil        = 0;

        // Define the JSON schema as a string for the prompt
        $jsonSchema = <<<JSON
{
  "type": "object",
  "properties": {
    "score": { "type": "number" },
    "details": { "type": "string" }
  },
  "required": ["score", "details"]
}
JSON;

        foreach ($segments as $i => $segment) {
            if ($i < $skipUntil) {
                continue;
            }
            $windowStart = (int) $segment['start'];
            $windowText  = '';

            foreach (array_slice($segments, $i) as $j => $windowSegment) {
                $windowText .= $windowSegment['text'] . ' ';
                $windowEnd      = (int) $windowSegment['end'];
                $windowDuration = $windowEnd - $windowStart;

                if ($windowDuration > $maxSeconds) {
                    break;
                }
                if ($windowDuration >= $minSeconds) {
                    $prompt  = 'Evalúa la relevancia del siguiente segmento: ' . trim($windowText);
                    $options = [
                        'model' => self::MODEL_NAME,
                        'tools' => [
                            [
                                'type' => 'web_search_preview',
                            ],
                        ],
                        'temperature'         => 0.1,
                        'max_output_tokens'   => 150,
                        'tool_choice'         => 'auto',
                        'parallel_tool_calls' => true,
                        'store'               => true,
                        'input'               => [
                            [
                                'role'    => 'system',
                                'content' => "Eres un experto en análisis de contenido de YouTube. Evalúa la relevancia del segmento de audio proporcionado y responde estrictamente en formato JSON conforme al siguiente esquema:\n{$jsonSchema}",
                            ],
                            [
                                'role'    => 'user',
                                'content' => $prompt,
                            ],
                        ],
                    ];
                    $response = $this->client->responses()->create($options);
                    dd($response);
                    $result = json_decode($content, true);

                    // Basic validation against the schema
                    if (
                        is_array($result) && isset($result['score']) && isset($result['details']) && $result['score'] > 0.5
                    ) {
                        $relevantSegments[] = [
                            'start'   => $windowStart,
                            'end'     => $windowEnd,
                            'text'    => trim($windowText),
                            'score'   => $result['score'],
                            'details' => $result['details'],
                        ];
                        $skipUntil = $i + $j + 1;
                        break;
                    }
                }
            }
        }

        return $relevantSegments;
    }

    public function downloadAudioPreviewFromYoutube(VideoId $videoId, int $minutes): string
    {
        $cacheKey   = md5($videoId->value() . "_{$minutes}min");
        $outputPath = self::AUDIO_PATH . $cacheKey . '_preview.mp3';

        if (file_exists($outputPath) && filesize($outputPath) > 0) {
            return $outputPath;
        }

        $duration    = $minutes * 60;
        $minutes     = floor($duration / 60);
        $seconds     = $duration % 60;
        $durationStr = sprintf('%02d:%02d', $minutes, $seconds);
        $command     = "yt-dlp --extract-audio --audio-format mp3 --download-sections '*00:00-{$durationStr}' -o '{$outputPath}' https://www.youtube.com/watch?v=" . $videoId->value();

        try {
            exec($command, $output, $resultCode);
        } catch (\Throwable $e) {
            \Log::error('Error downloading audio preview: ' . $e->getMessage(), [
                'videoId' => $videoId->value(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to download audio preview for video ID: ' . $videoId->value());
        }

        if ($resultCode !== 0 || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Audio preview download failed or file is invalid for video ID: ' . $videoId->value());
        }

        return $outputPath;
    }

    private function transcribeWithWhisper(string $audioPath): array
    {
        $cacheFile = self::JSON_PATH . md5($audioPath) . '_transcription.json';

        if (file_exists($cacheFile)) {
            $segments = json_decode(file_get_contents($cacheFile), true);

            return is_array($segments) ? $segments : [];
        }

        try {
            $client  = \OpenAI::client(config('services.openai.key'));
            $options = [
                'model'                   => 'whisper-1',
                'file'                    => fopen($audioPath, 'rb'),
                'response_format'         => 'verbose_json',
                'language'                => 'es',
                'prompt'                  => 'Transcribe el audio al español con la máxima precisión posible.',
                'timestamp_granularities' => ['segment', 'word'],
            ];

            $response = $client->audio()->transcribe($options);

            if (!isset($response['segments']) || !is_array($response['segments'])) {
                throw new \UnexpectedValueException('Invalid response format from Whisper API.');
            }

            file_put_contents($cacheFile, json_encode($response['segments']));

            return $response['segments'];
        } catch (\Throwable $e) {
            \Log::error('Error during transcription: ' . $e->getMessage(), [
                'audioPath' => $audioPath,
                'trace'     => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
