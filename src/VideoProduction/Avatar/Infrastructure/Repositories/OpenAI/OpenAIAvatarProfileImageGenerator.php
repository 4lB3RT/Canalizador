<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI;

use Helmreel\Shared\Shared\Domain\Services\HttpClient;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarProfileImageGenerator;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarData;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final readonly class OpenAIAvatarProfileImageGenerator implements AvatarProfileImageGenerator
{
    private const string IMAGE_MODEL = 'gpt-image-1.5';

    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    public function generate(AvatarData $data, Category $category): LocalPath
    {
        $prompt = $this->buildPrompt($data, $category);

        $response = Prism::image()
            ->using(Provider::OpenAI, self::IMAGE_MODEL)
            ->withPrompt($prompt)
            ->withProviderOptions(['size' => '1024x1024', 'quality' => 'medium'])
            ->generate();

        $avatarsDir = storage_path('app/avatars');
        if (!File::isDirectory($avatarsDir)) {
            File::makeDirectory($avatarsDir, 0755, true);
        }

        $imagePath = $avatarsDir . '/' . Str::uuid()->toString() . '.png';
        $this->saveImage($response->firstImage(), $imagePath);

        return LocalPath::fromString($imagePath);
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

    private function saveImage(object $image, string $savePath): void
    {
        if (!empty($image->base64)) {
            $bytes = base64_decode($image->base64, true);
            if ($bytes === false) {
                throw new \RuntimeException('Failed to decode base64 image data');
            }
            File::put($savePath, $bytes);

            return;
        }

        if (!empty($image->url)) {
            $body = $this->httpClient->get($image->url, [], 60)->body();
            if (empty($body)) {
                throw new \RuntimeException('Empty image data received from URL');
            }
            File::put($savePath, $body);

            return;
        }

        throw new \RuntimeException('No image data received from OpenAI');
    }
}
