<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Veo;

use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\ImageMimeType;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\Video\Domain\Repositories\VideoExtender;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\ValueObjects\AspectRatio;
use Canalizador\Video\Domain\ValueObjects\Resolution;
use Canalizador\Video\Domain\ValueObjects\VideoDuration;
use Illuminate\Support\Facades\File;

final readonly class VeoVideoRepository implements VideoGenerator, VideoContentRetriever, VideoExtender
{
    private const string API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';
    private const int MAX_REFERENCE_IMAGES = 3;

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
    public function generate(VideoPrompt $videoPrompt): string
    {
        $model = config('veo.model', 'veo-3.1-generate-preview');
        $url = self::API_BASE_URL . "/models/{$model}:predictLongRunning";

        $headers = [
            'x-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $referenceImages = $this->buildReferenceImages($videoPrompt);
        $hasReferenceImages = !empty($referenceImages);

        $aspectRatio = $hasReferenceImages
            ? AspectRatio::forReferenceImages()
            : AspectRatio::fromString(config('veo.aspect_ratio', '16:9'));

        $duration = $hasReferenceImages
            ? VideoDuration::forReferenceImages()
            : VideoDuration::fromSeconds(config('veo.duration', 8));

        $resolution = Resolution::fromString('720p');

        $instance = [
            'prompt' => $videoPrompt->toPromptString(),
        ];

        if ($hasReferenceImages) {
            $instance['referenceImages'] = $referenceImages;
        }

        $data = [
            'instances' => [$instance],
            'parameters' => [
                'aspectRatio' => $aspectRatio->value,
                'resolution' => $resolution->value,
                'durationSeconds' => $duration->value,
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
        $operationName = $video->generationId()->value();

        try {
            $videoUrl = $this->pollForCompletion($operationName);
            $videoContent = $this->downloadVideo($videoUrl);

            if (empty($videoContent)) {
                throw VideoGenerationFailed::apiError('Empty video content received from Veo');
            }

            $tmpDir = storage_path('app/videos');
            if (!File::exists($tmpDir)) {
                File::makeDirectory($tmpDir, 0755, true);
            }

            $filename = "{$video->id()->value()}.mp4";
            $filePath = $tmpDir . '/' . $filename;

            File::put($filePath, $videoContent);

            $video->markAsCompleted(new LocalPath($filePath), new DateTime(new \DateTimeImmutable()));
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function extend(Url $lastVideoUri): string
    {
        $model = config('veo.model', 'veo-3.1-generate-preview');
        $url = self::API_BASE_URL . "/models/{$model}:predictLongRunning";

        $headers = [
            'x-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $data = [
            'instances' => [
                [
                    'prompt' => 'Continue the video naturally maintaining visual continuity.',
                    'video' => [
                        'uri' => $lastVideoUri->value(),
                    ],
                ],
            ],
            'parameters' => [
                'aspectRatio' => config('veo.aspect_ratio', '9:16'),
                'resolution' => '720p',
            ],
        ];

        try {
            $response = $this->httpClient->post($url, $headers, $data, 60);
            $this->responseValidator->validateSuccess($response, 'Veo video extension');

            $responseData = $response->json();

            if (!isset($responseData['name'])) {
                throw VideoGenerationFailed::apiError('No operation ID received from Veo extension API');
            }

            return $responseData['name'];
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    private function pollForCompletion(string $operationName): string
    {
        $url = self::API_BASE_URL . '/' . $operationName;
        $headers = [
            'x-goog-api-key' => $this->apiKey,
        ];

        $pollingInterval = config('veo.polling.interval', 10);
        $maxAttempts = config('veo.polling.max_attempts', 60);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            sleep($pollingInterval);

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
        }

        throw VideoGenerationFailed::apiError('Veo video generation timed out after polling');
    }

    /**
     * @throws VideoGenerationFailed
     */
    private function extractVideoUrl(array $responseData): string
    {
        $uri = $responseData['response']['generateVideoResponse']['generatedSamples'][0]['video']['uri'] ?? null;

        if ($uri !== null) {
            return $uri;
        }

        $uri = $responseData['response']['generatedSamples'][0]['video']['uri'] ?? null;

        if ($uri !== null) {
            return $uri;
        }

        throw VideoGenerationFailed::apiError('No video URL found in Veo response');
    }

    private function downloadVideo(string $videoUrl): string
    {
        $headers = [
            'x-goog-api-key' => $this->apiKey,
        ];

        $response = $this->httpClient->get($videoUrl, $headers, 120);
        $this->responseValidator->validateSuccess($response, 'Veo video download');

        return $response->body();
    }

    private function buildReferenceImages(VideoPrompt $videoPrompt): array
    {
        if ($videoPrompt->host() === null) {
            return [];
        }

        $images = array_slice($videoPrompt->host()->images()->items(), 0, self::MAX_REFERENCE_IMAGES);

        return array_filter(array_map(function ($avatarImage) {
            $imagePath = $avatarImage->path()->value();

            if (!File::exists($imagePath)) {
                return null;
            }

            return [
                'image' => [
                    'bytesBase64Encoded' => base64_encode(File::get($imagePath)),
                    'mimeType' => ImageMimeType::fromFilePath($imagePath)->value,
                ],
                'referenceType' => 'asset',
            ];
        }, $images));
    }
}
