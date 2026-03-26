<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs;

use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\VideoProduction\Voice\Domain\Exceptions\VoiceGenerationFailed;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceGenerator;
use Illuminate\Support\Str;

final class ElevenLabsVoiceGenerator implements VoiceGenerator
{
    private const BASE_URL = 'https://api.elevenlabs.io/v1/speech-to-speech';

    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient,
        private readonly HttpResponseValidator $responseValidator,
        private readonly string $modelId,
        private readonly string $outputFormat,
        private readonly bool $removeBackgroundNoise,
        private readonly int $timeout,
        private readonly float $stability,
        private readonly float $similarityBoost,
    ) {
    }

    public function generate(string $sourceAudioPath, string $elevenLabsVoiceId): string
    {
        if (!file_exists($sourceAudioPath)) {
            throw VoiceGenerationFailed::apiError("Audio file not found: {$sourceAudioPath}");
        }

        $url = sprintf(
            '%s/%s?output_format=%s',
            self::BASE_URL,
            $elevenLabsVoiceId,
            $this->outputFormat,
        );

        try {
            $response = $this->httpClient->postMultipart(
                url: $url,
                headers: ['xi-api-key' => $this->apiKey],
                data: [
                    'model_id' => $this->modelId,
                    'remove_background_noise' => $this->removeBackgroundNoise ? 'true' : 'false',
                    'voice_settings' => json_encode([
                        'stability' => $this->stability,
                        'similarity_boost' => $this->similarityBoost,
                    ]),
                ],
                files: ['audio' => $sourceAudioPath],
                timeout: $this->timeout,
            );

            $this->responseValidator->validateSuccess($response, 'ElevenLabs Speech-to-Speech');
        } catch (\Throwable $e) {
            throw VoiceGenerationFailed::apiError($e->getMessage());
        }

        $directory = storage_path('app/voices');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . Str::uuid()->toString() . '.mp3';
        file_put_contents($filePath, $response->body());

        return $filePath;
    }
}
