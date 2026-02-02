<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Veo;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Video\Application\UseCases\GenerateVideo\ValueObjects\VideoPrompt;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Illuminate\Support\Facades\File;

final readonly class VeoVideoRepository implements VideoGenerator, VideoContentRetriever
{
    private const string API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(
        private string $apiKey,
        private HttpClient $httpClient,
        private HttpResponseValidator $responseValidator
    ) {
        if (empty($this->apiKey)) {
            throw VideoGenerationFailed::apiError('Google Veo API key is not configured');
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function generate(VideoPrompt $videoPrompt, Channel $channel): string
    {
        $model = config('veo.model', 'veo-3.1-generate-preview');
        $url = self::API_BASE_URL . "/models/{$model}:generateContent";

        $headers = [
            'x-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $duration = config('veo.duration', 8);
        $aspectRatio = config('veo.aspect_ratio', '16:9');

        $parts = [
            ['text' => $videoPrompt->toPromptString()],
        ];

        $avatarImageData = $this->getAvatarImageBase64($videoPrompt);
        if ($avatarImageData !== null) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => $avatarImageData['mimeType'],
                    'data' => $avatarImageData['data'],
                ],
            ];
        }

        $data = [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['VIDEO'],
                'videoDurationSeconds' => $duration,
                'videoAspectRatio' => $aspectRatio,
            ],
        ];

        try {
            $response = $this->httpClient->post($url, $headers, $data, 60);
            $this->responseValidator->validateSuccess($response, 'Veo video generation');

            $responseData = $response->json();

            if (!isset($responseData['name'])) {
                throw VideoGenerationFailed::apiError('No operation ID received from Veo API');
            }

            return $responseData['name'];
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function retrieve(Video $video): void
    {
        $operationId = $video->generationId()->value();

        try {
            $videoUrl = $this->pollForCompletion($operationId);
            $videoContent = $this->downloadVideo($videoUrl);

            if (empty($videoContent)) {
                throw VideoGenerationFailed::apiError('Empty video content received from Veo');
            }

            $tmpDir = storage_path('tmp');
            if (!File::exists($tmpDir)) {
                File::makeDirectory($tmpDir, 0755, true);
            }

            $filename = "{$video->id()->value()}.mp4";
            $filePath = $tmpDir . '/' . $filename;

            File::put($filePath, $videoContent);
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    private function pollForCompletion(string $operationId): string
    {
        $url = self::API_BASE_URL . "/{$operationId}";
        $headers = [
            'x-goog-api-key' => $this->apiKey,
        ];

        $pollingInterval = config('veo.polling.interval', 5);
        $maxAttempts = config('veo.polling.max_attempts', 120);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $response = $this->httpClient->get($url, $headers, 30);
            $this->responseValidator->validateSuccess($response, 'Veo operation status');

            $responseData = $response->json();

            if (isset($responseData['done']) && $responseData['done'] === true) {
                if (isset($responseData['error'])) {
                    throw VideoGenerationFailed::apiError(
                        'Veo generation failed: ' . ($responseData['error']['message'] ?? 'Unknown error')
                    );
                }

                return $this->extractVideoUrl($responseData);
            }

            sleep($pollingInterval);
        }

        throw VideoGenerationFailed::apiError('Veo video generation timed out after polling');
    }

    /**
     * @throws VideoGenerationFailed
     */
    private function extractVideoUrl(array $responseData): string
    {
        $candidates = $responseData['response']['candidates'] ?? [];

        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['fileData']['fileUri'])) {
                    return $part['fileData']['fileUri'];
                }
                if (isset($part['videoMetadata']['videoUri'])) {
                    return $part['videoMetadata']['videoUri'];
                }
            }
        }

        if (isset($responseData['response']['video']['uri'])) {
            return $responseData['response']['video']['uri'];
        }

        throw VideoGenerationFailed::apiError('No video URL found in Veo response');
    }

    /**
     * @throws VideoGenerationFailed
     */
    private function downloadVideo(string $videoUrl): string
    {
        $headers = [
            'x-goog-api-key' => $this->apiKey,
        ];

        $response = $this->httpClient->get($videoUrl, $headers, 120);
        $this->responseValidator->validateSuccess($response, 'Veo video download');

        return $response->body();
    }

    /**
     * @return array{mimeType: string, data: string}|null
     */
    private function getAvatarImageBase64(VideoPrompt $videoPrompt): ?array
    {
        if ($videoPrompt->host() === null) {
            return null;
        }

        $avatarImage = $videoPrompt->host()->images()->first();

        if ($avatarImage === null) {
            return null;
        }

        $imagePath = $avatarImage->path()->value();

        if (!File::exists($imagePath)) {
            return null;
        }

        $imageContent = File::get($imagePath);
        $mimeType = $this->getMimeType($imagePath);

        return [
            'mimeType' => $mimeType,
            'data' => base64_encode($imageContent),
        ];
    }

    private function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
