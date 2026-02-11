<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Veo;

use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoExtender;

final readonly class VeoVideoExtender implements VideoExtender
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
    public function extend(Url $lastVideoUri): string
    {
        $model = config('veo.model', 'veo-3.1-generate-preview');
        $url = self::API_BASE_URL . "/models/{$model}:predictLongRunning";

        $headers = [
            'x-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        // TODO(human): Build the extension prompt for Veo
        $extensionPrompt = $this->buildExtensionPrompt();

        $data = [
            'instances' => [
                [
                    'prompt' => $extensionPrompt,
                    'video' => [
                        'uri' => $lastVideoUri->value(),
                    ],
                ],
            ],
            'parameters' => [
                'aspectRatio' => config('veo.aspect_ratio', '9:16'),
                'resolution' => '720p',
                'numberOfVideos' => 1,
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
}
