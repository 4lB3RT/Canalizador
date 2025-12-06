<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Sora;

use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

final class SoraVideoRepository implements VideoGenerator, VideoContentRetriever
{
    private const string API_BASE_URL = 'https://api.openai.com/v1';
    private const string MODEL = 'sora-2';

    public function __construct(
        private readonly string $apiKey
    ) {
        if (empty($this->apiKey)) {
            throw VideoGenerationFailed::apiError('OpenAI API key is not configured');
        }
    }

    /**
     * @throws VideoGenerationFailed
     * @throws ConnectionException
     */
    public function generate(string $prompt): string
    {
        $requestBody = [
            'model' => self::MODEL,
            'prompt' => $prompt,
            'seconds' => '12',
            'size' => '1280x720',
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(self::API_BASE_URL . '/videos', $requestBody);

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

    /**
     * @throws VideoGenerationFailed
     * @throws ConnectionException
     */
    public function retrieve(string $videoId): string
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->timeout(60)->get(self::API_BASE_URL . "/videos/{$videoId}/content");

        if (!$response->successful()) {
            $statusCode = $response->status();
            $errorMessage = $this->extractErrorMessage($response);

            throw VideoGenerationFailed::apiError(
                sprintf('HTTP %d: %s', $statusCode, $errorMessage)
            );
        }

        $videoContent = $response->body();

        if (empty($videoContent)) {
            throw VideoGenerationFailed::apiError('Empty video content received');
        }

        $tmpDir = storage_path('tmp');
        if (!File::exists($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }

        $extension = $this->getVideoExtension($response->header('Content-Type'));
        $filename = "{$videoId}.{$extension}";
        $filePath = $tmpDir . '/' . $filename;

        File::put($filePath, $videoContent);

        return $filePath;
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
