<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Infrastructure\Repositories\Veo;

use Canalizador\Shared\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\ImageMimeType;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Url;
use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoExtender;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoGenerator;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\AspectRatio;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Resolution;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoDuration;
use Illuminate\Support\Facades\File;

final readonly class VeoVideoRepository implements VideoGenerator, VideoContentRetriever, VideoExtender
{
    private const string API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';
    private const int MAX_REFERENCE_IMAGES = 5;

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
    public function generate(VideoPrompt $videoPrompt, ?Resolution $resolution = null): string
    {
        $model = config('veo.model', 'veo-3.1-generate-preview');
        $url = self::API_BASE_URL . "/models/{$model}:predictLongRunning";

        $headers = [
            'x-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $referenceImages = $this->buildReferenceImages($videoPrompt);
        $hasReferenceImages = !empty($referenceImages);

        $aspectRatio = AspectRatio::fromString(config('veo.aspect_ratio', '16:9'));

        $duration = $hasReferenceImages
            ? VideoDuration::forReferenceImages()
            : VideoDuration::fromSeconds(config('veo.duration', 8));

        $resolution = $resolution ?? Resolution::fromString(config('veo.resolution', '720p'));

        $instance = [
            'prompt' => $videoPrompt->toPromptString(),
        ];

        if ($hasReferenceImages) {
            $instance['referenceImages'] = $referenceImages;
        }

        $firstFramePath = $videoPrompt->firstFramePath();

        if ($firstFramePath !== null && File::exists($firstFramePath)) {
            $frameData = [
                'bytesBase64Encoded' => base64_encode(File::get($firstFramePath)),
                'mimeType' => ImageMimeType::fromFilePath($firstFramePath)->value,
            ];
            $instance['image'] = $frameData;
            $instance['lastFrame'] = $frameData;
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
    public function extend(Url $lastVideoUri, string $clipPrompt): string
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
                    'prompt' => $clipPrompt,
                    'video' => [
                        'uri' => $lastVideoUri->value(),
                    ],
                ],
            ],
            'parameters' => [
                'aspectRatio' => config('veo.aspect_ratio', '9:16'),
                'resolution' => Resolution::HD->value,
                'durationSeconds' => config('veo.duration', 8),
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
        $raiReasons = $responseData['response']['generateVideoResponse']['raiMediaFilteredReasons'] ?? null;

        if ($raiReasons !== null) {
            throw VideoGenerationFailed::apiError(
                'Veo RAI filter blocked video: ' . implode(', ', $raiReasons)
            );
        }

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
        $referenceImages = [];

        if ($videoPrompt->host() !== null) {
            $avatarImages = array_slice($videoPrompt->host()->images()->items(), 0, self::MAX_REFERENCE_IMAGES);

            foreach ($avatarImages as $avatarImage) {
                $imagePath = $avatarImage->path()->value();

                if (!File::exists($imagePath)) {
                    continue;
                }

                $referenceImages[] = [
                    'image' => [
                        'bytesBase64Encoded' => base64_encode(File::get($imagePath)),
                        'mimeType' => ImageMimeType::fromFilePath($imagePath)->value,
                    ],
                    'referenceType' => 'asset',
                ];
            }
        }

        foreach ($videoPrompt->referenceImagePaths() as $imagePath) {
            if (!File::exists($imagePath)) {
                continue;
            }

            $referenceImages[] = [
                'image' => [
                    'bytesBase64Encoded' => base64_encode(File::get($imagePath)),
                    'mimeType' => ImageMimeType::fromFilePath($imagePath)->value,
                ],
                'referenceType' => 'asset',
            ];
        }

        return array_slice($referenceImages, 0, self::MAX_REFERENCE_IMAGES);
    }
}
