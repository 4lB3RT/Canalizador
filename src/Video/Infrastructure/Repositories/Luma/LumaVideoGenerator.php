<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Luma;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Video\Application\UseCases\GenerateVideo\ValueObjects\VideoPrompt;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoGenerator;

final readonly class LumaVideoGenerator implements VideoGenerator
{
    private const string API_BASE_URL = 'https://api.lumalabs.ai/dream-machine/v1';
    private const string MODEL = 'ray-flash-2';

    public function __construct(
        private string $apiKey,
        private HttpClient $httpClient,
        private HttpResponseValidator $responseValidator
    ) {
        if (empty($this->apiKey)) {
            throw VideoGenerationFailed::apiError('Luma API key is not configured');
        }
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function generate(VideoPrompt $videoPrompt, Channel $channel): string
    {
        $url = self::API_BASE_URL . '/generations/video';
        $headers = [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $data = [
            'prompt' => $videoPrompt->toPromptString(),
            'model' => self::MODEL,
            'duration' => '5s',
            'aspect_ratio' => '16:9',
            'resolution' => '540p',
        ];

        try {
            $response = $this->httpClient->post($url, $headers, $data, 30);
            $this->responseValidator->validateSuccess($response, 'Luma video generation');
            $this->responseValidator->validateJsonKey($response, 'id', 'Luma video generation');

            $responseData = $response->json();
            return $responseData['id'];
        } catch (\RuntimeException $e) {
            throw VideoGenerationFailed::apiError($e->getMessage());
        }
    }
}
