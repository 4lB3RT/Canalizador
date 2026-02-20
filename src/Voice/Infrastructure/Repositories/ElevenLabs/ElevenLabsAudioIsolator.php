<?php

declare(strict_types=1);

namespace Canalizador\Voice\Infrastructure\Repositories\ElevenLabs;

use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Voice\Domain\Exceptions\VoiceGenerationFailed;
use Canalizador\Voice\Domain\Repositories\AudioIsolator;
use Illuminate\Support\Str;

final class ElevenLabsAudioIsolator implements AudioIsolator
{
    private const URL = 'https://api.elevenlabs.io/v1/audio-isolation';

    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient,
        private readonly HttpResponseValidator $responseValidator,
        private readonly int $timeout,
    ) {
    }

    public function isolate(string $audioPath): string
    {
        if (!file_exists($audioPath)) {
            throw VoiceGenerationFailed::apiError("Audio file not found: {$audioPath}");
        }

        try {
            $response = $this->httpClient->postMultipart(
                url: self::URL,
                headers: ['xi-api-key' => $this->apiKey],
                data: [],
                files: ['audio' => $audioPath],
                timeout: $this->timeout,
            );

            $this->responseValidator->validateSuccess($response, 'ElevenLabs Audio Isolation');
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
