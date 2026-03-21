<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Infrastructure\Repositories\Sora;

use Canalizador\VideoProduction\Shared\Domain\Services\HttpClient;
use Canalizador\VideoProduction\Shared\Domain\Services\HttpResponse;
use Canalizador\VideoProduction\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoGenerator;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Resolution;
use Illuminate\Support\Facades\File;

final readonly class SoraVideoRepository implements VideoGenerator, VideoContentRetriever
{
    private const string API_BASE_URL = 'https://api.openai.com/v1';

    public function __construct(
        private string $apiKey,
        private HttpClient $httpClient,
        private HttpResponseValidator $responseValidator
    ) {
        if (empty($this->apiKey)) {
            throw VideoGenerationFailed::apiError('OpenAI API key is not configured');
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function generate(VideoPrompt $videoPrompt, ?Resolution $resolution = null): string
    {
        $url = self::API_BASE_URL . '/videos';
        $headers = [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $model = config('sora.model', 'sora-2-pro');
        $duration = config('sora.duration', 9);
        $resolution = config('sora.resolution', '1280x720');

        $this->validateResolution($resolution);

        $data = [
            'model' => $model,
            'prompt' => $videoPrompt->toPromptString(),
            'seconds' => (string) $duration,
            'size' => $resolution,
        ];

        $avatarImagePath = $this->getAvatarImagePath($videoPrompt);

        try {
            $response = $avatarImagePath !== null
                ? $this->httpClient->postMultipart($url, $headers, $data, ['input_reference' => $avatarImagePath], 30)
                : $this->httpClient->post($url, $headers, $data, 30);
            $this->responseValidator->validateSuccess($response, 'Sora video generation');
            $this->responseValidator->validateJsonKey($response, 'id', 'Sora video generation');

            $responseData = $response->json();
            return $responseData['id'];
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function retrieve(Video $video): void
    {
        $url = self::API_BASE_URL . "/videos/{$video->generationId()->value()}/content";
        $headers = [
            'Authorization' => "Bearer {$this->apiKey}",
        ];

        try {
            $response = $this->httpClient->get($url, $headers, 60);
            $this->responseValidator->validateSuccess($response, 'Sora video content retrieval');

            $videoContent = $response->body();

            if (empty($videoContent)) {
                throw VideoGenerationFailed::apiError('Empty video content received');
            }

            $tmpDir = storage_path('tmp');
            if (!File::exists($tmpDir)) {
                File::makeDirectory($tmpDir, 0755, true);
            }

            $extension = $this->getVideoExtension($response->header('Content-Type'));
            $filename = "{$video->id()->value()}.{$extension}";
            $filePath = $tmpDir . '/' . $filename;

            File::put($filePath, $videoContent);
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }

    private function getVideoExtension(?string $contentType): string
    {
        if ($contentType === null) {
            return 'mp4';
        }

        return match (true) {
            str_contains($contentType, 'video/mp4') => 'mp4',
            str_contains($contentType, 'video/webm') => 'webm',
            str_contains($contentType, 'video/quicktime') => 'mov',
            default => 'mp4',
        };
    }

    /**
     * @throws VideoGenerationFailed
     */
    private function validateResolution(string $resolution): void
    {
        $availableResolutions = config('sora.available_resolutions', [
            '1280x720',
            '720x1280',
            '1792x1024',
            '1024x1792',
        ]);

        if (!in_array($resolution, $availableResolutions, true)) {
            throw VideoGenerationFailed::apiError(
                "Invalid resolution: {$resolution}. Supported values are: " . implode(', ', $availableResolutions)
            );
        }
    }

    private function getAvatarImagePath(VideoPrompt $videoPrompt): ?string
    {
        if ($videoPrompt->host() === null) {
            return null;
        }

        $avatarImage = $videoPrompt->host()->images()->first();

        if ($avatarImage === null) {
            return null;
        }

        $imagePath = $avatarImage->path()->value();

        return File::exists($imagePath) ? $imagePath : null;
    }
}
