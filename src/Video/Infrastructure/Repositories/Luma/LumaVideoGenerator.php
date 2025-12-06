<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Luma;

use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class LumaVideoGenerator implements VideoGenerator
{
    private const string API_BASE_URL = 'https://api.lumalabs.ai/dream-machine/v1';
    private const string MODEL = 'ray-flash-2';

    public function __construct(
        private readonly string $apiKey
    ) {
        if (empty($this->apiKey)) {
            throw VideoGenerationFailed::apiError('Luma API key is not configured');
        }
    }

    /**
     * @throws VideoGenerationFailed
     * @throws ConnectionException
     */
    public function generate(string $prompt): string
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(self::API_BASE_URL . '/generations/video', [
            'prompt' => $prompt,
            'model' => self::MODEL,
            'duration' => '5s',
            'aspect_ratio' => '16:9',
            'resolution' => '540p',
        ]);

        if (!$response->successful()) {
            $statusCode = $response->status();
            $errorMessage = $this->extractErrorMessage($response);

            throw VideoGenerationFailed::apiError(
                sprintf('HTTP %d: %s', $statusCode, $errorMessage)
            );
        }

        $data = $response->json();

        if (!isset($data['id'])) {
            throw VideoGenerationFailed::apiError('Invalid response: missing generation ID');
        }

        return $data['id'];
    }

    private function extractErrorMessage(Response $response): string
    {
        $body = $response->body();

        try {
            $data = $response->json();
            if (isset($data['error']['message'])) {
                return $data['error']['message'];
            }
            if (isset($data['error'])) {
                return is_string($data['error']) ? $data['error'] : json_encode($data['error']);
            }
            if (isset($data['message'])) {
                return $data['message'];
            }
        } catch (\Exception $e) {
        }

        return strlen($body) > 200 ? substr($body, 0, 200) . '...' : $body;
    }
}
