<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\Gemini;

use Helmreel\Shared\Shared\Domain\Services\HttpClient;
use Helmreel\Shared\Shared\Domain\Services\HttpResponseValidator;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarProfileImageGenerator;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarData;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final readonly class GeminiAvatarProfileImageGenerator implements AvatarProfileImageGenerator
{
    private const string BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(
        private string $apiKey,
        private string $model,
        private HttpClient $httpClient,
        private HttpResponseValidator $responseValidator,
        private int $timeout = 120,
    ) {
    }

    public function generate(AvatarData $data, Category $category): LocalPath
    {
        $prompt = $this->buildPrompt($data, $category);

        $url = self::BASE_URL . "/{$this->model}:generateContent";

        $response = $this->httpClient->post(
            $url,
            [
                'x-goog-api-key' => $this->apiKey,
                'Content-Type'   => 'application/json',
            ],
            [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE'],
                ],
            ],
            $this->timeout,
        );

        $this->responseValidator->validateSuccess($response, 'Gemini avatar image generation');

        $json = $response->json();
        $base64 = $this->extractImageData($json);

        $avatarsDir = storage_path('app/avatars');
        if (!File::isDirectory($avatarsDir)) {
            File::makeDirectory($avatarsDir, 0755, true);
        }

        $imagePath = $avatarsDir . '/' . Str::uuid()->toString() . '.png';

        $bytes = base64_decode($base64, true);
        if ($bytes === false) {
            throw new \RuntimeException('Failed to decode Gemini image data');
        }
        File::put($imagePath, $bytes);

        return LocalPath::fromString($imagePath);
    }

    private function extractImageData(array $json): string
    {
        $parts = $json['candidates'][0]['content']['parts'] ?? [];

        foreach ($parts as $part) {
            $data = $part['inlineData']['data'] ?? $part['inline_data']['data'] ?? null;
            if (is_string($data) && $data !== '') {
                return $data;
            }
        }

        throw new \RuntimeException('No image data received from Gemini');
    }

    private function buildPrompt(AvatarData $data, Category $category): string
    {
        return str_replace(
            ['{avatar_name}', '{biography}', '{presentation_style}', '{category}'],
            [
                $data->name->value(),
                $data->biography->value(),
                $data->presentationStyle->value,
                $category->value,
            ],
            (string) config('prompts.avatar.profile_image_generator.prompt'),
        );
    }
}
