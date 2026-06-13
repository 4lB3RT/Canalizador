<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs;

use Helmreel\Shared\Shared\Domain\Services\HttpClient;
use Helmreel\Shared\Shared\Domain\Services\HttpResponseValidator;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceBlocked;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceGenerationFailed;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceSettings;
use Illuminate\Support\Str;

final class ElevenLabsTextToSpeech
{
    private const BASE_URL = 'https://api.elevenlabs.io/v1/text-to-speech';

    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient,
        private readonly HttpResponseValidator $responseValidator,
        private readonly string $modelId,
        private readonly string $outputFormat,
        private readonly int $timeout,
    ) {
    }

    public function synthesize(string $text, string $elevenLabsVoiceId, VoiceSettings $settings): string
    {
        $url = sprintf(
            '%s/%s?output_format=%s',
            self::BASE_URL,
            $elevenLabsVoiceId,
            $this->outputFormat,
        );

        try {
            $response = $this->httpClient->post(
                url: $url,
                headers: ['xi-api-key' => $this->apiKey],
                data: [
                    'text' => $text,
                    'model_id' => $this->modelId,
                    'voice_settings' => [
                        'stability' => $settings->stability,
                        'similarity_boost' => $settings->similarityBoost,
                        'style' => $settings->style,
                        'speed' => $settings->speed,
                        'use_speaker_boost' => $settings->useSpeakerBoost,
                    ],
                ],
                timeout: $this->timeout,
            );

            $this->responseValidator->validateSuccess($response, 'ElevenLabs Text-to-Speech');
        } catch (\Throwable $e) {
            if ($this->isBlockedVoiceError($e->getMessage())) {
                throw VoiceBlocked::byPlatform();
            }

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

    private function isBlockedVoiceError(string $message): bool
    {
        return str_contains($message, 'detected_blocked_voice')
            || str_contains($message, 'voice_access_denied');
    }
}
