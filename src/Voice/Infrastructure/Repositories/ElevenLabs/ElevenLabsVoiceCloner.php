<?php

declare(strict_types=1);

namespace Canalizador\Voice\Infrastructure\Repositories\ElevenLabs;

use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Voice\Domain\Exceptions\VoiceGenerationFailed;
use Canalizador\Voice\Domain\Repositories\VoiceCloner;

final class ElevenLabsVoiceCloner implements VoiceCloner
{
    private const URL = 'https://api.elevenlabs.io/v1/voices/add';

    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient,
        private readonly HttpResponseValidator $responseValidator,
        private readonly int $timeout,
    ) {
    }

    public function clone(string $audioPath, string $name): string
    {
        if (!file_exists($audioPath)) {
            throw VoiceGenerationFailed::apiError("Audio file not found: {$audioPath}");
        }

        try {
            $response = $this->httpClient->postMultipart(
                url: self::URL,
                headers: ['xi-api-key' => $this->apiKey],
                data: ['name' => $name],
                files: ['files' => $audioPath],
                timeout: $this->timeout,
            );

            $this->responseValidator->validateSuccess($response, 'ElevenLabs Voice Clone');
        } catch (\Throwable $e) {
            throw VoiceGenerationFailed::apiError($e->getMessage());
        }

        $json = $response->json();

        if (!isset($json['voice_id'])) {
            throw VoiceGenerationFailed::apiError('Missing voice_id in response');
        }

        return $json['voice_id'];
    }
}
