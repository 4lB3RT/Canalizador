<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Infrastructure\Repositories\Veo;

use Canalizador\VideoProduction\Clip\Domain\Repositories\ClipDownloader;
use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

final readonly class VeoClipDownloader implements ClipDownloader
{
    private const string API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(
        private string $apiKey,
        private HttpClient $httpClient,
        private HttpResponseValidator $responseValidator
    ) {
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function download(GenerationId $generationId, LocalPath $outputPath): array
    {
        $operationName = $generationId->value();

        try {
            $result = $this->pollForCompletion($operationName);
            $videoContent = $this->downloadVideo($result['videoUrl']);

            if (empty($videoContent)) {
                throw VideoGenerationFailed::apiError('Empty video content received from Veo');
            }

            $dir = dirname($outputPath->value());
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            File::put($outputPath->value(), $videoContent);

            return [
                'localPath' => $outputPath,
                'videoUri' => Url::fromString($this->toFileUri($result['videoUrl'])),
            ];
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }

    /**
     * @return array{videoUrl: string}
     * @throws VideoGenerationFailed
     */
    private function pollForCompletion(string $operationName): array
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
            $this->responseValidator->validateSuccess($response, 'Veo clip polling');

            $responseData = $response->json();

            if (isset($responseData['done']) && $responseData['done'] === true) {
                if (isset($responseData['error'])) {
                    throw VideoGenerationFailed::apiError(
                        'Veo clip generation failed: ' . ($responseData['error']['message'] ?? 'Unknown error')
                    );
                }

                return [
                    'videoUrl' => $this->extractVideoUrl($responseData),
                ];
            }
        }

        throw VideoGenerationFailed::apiError('Veo clip generation timed out after polling');
    }

    private function extractVideoUrl(array $responseData): string
    {
        $raiReasons = $responseData['response']['generateVideoResponse']['raiMediaFilteredReasons'] ?? null;

        if ($raiReasons !== null) {
            throw VideoGenerationFailed::apiError(
                'Veo RAI filter blocked clip: ' . implode(', ', $raiReasons)
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

        Log::error('Veo clip response structure', ['response' => $responseData]);

        throw VideoGenerationFailed::apiError('No video URL found in Veo clip response');
    }

    private function toFileUri(string $downloadUrl): string
    {
        return preg_replace('/:download\?alt=media$/', '', $downloadUrl);
    }

    private function downloadVideo(string $videoUrl): string
    {
        $headers = [
            'x-goog-api-key' => $this->apiKey,
        ];

        $response = $this->httpClient->get($videoUrl, $headers, 120);
        $this->responseValidator->validateSuccess($response, 'Veo clip download');

        return $response->body();
    }
}
