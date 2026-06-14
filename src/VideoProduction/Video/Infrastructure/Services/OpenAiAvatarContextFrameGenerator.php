<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Services;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\Services\HttpClient;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarMediaType;
use Helmreel\Shared\Media\Domain\Entities\Media;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaType;
use Helmreel\VideoProduction\Video\Domain\Services\AvatarContextFrameGenerator;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\AspectRatio;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Image;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image as SpatieImage;

final readonly class OpenAiAvatarContextFrameGenerator implements AvatarContextFrameGenerator
{
    private const string IMAGE_MODEL = 'gpt-image-1.5';

    public function __construct(
        private string $apiKey,
        private MediaRepository $mediaRepository,
        private HttpClient $httpClient,
        private Clock $clock,
    ) {
    }

    public function frameFor(Avatar $avatar, VideoCategory $category, AspectRatio $aspectRatio): ?string
    {
        $cached = $this->cachedFramePath($avatar, $category, $aspectRatio);
        if ($cached !== null) {
            return $cached;
        }

        $profilePath = $avatar->profileImagePath()->value();
        if (!File::exists($profilePath)) {
            return null;
        }

        try {
            $prompt = $this->buildPrompt($avatar, $category);

            $response = Prism::image()
                ->using(Provider::OpenAI, self::IMAGE_MODEL)
                ->withPrompt($prompt, [Image::fromLocalPath($profilePath)])
                ->withProviderOptions(['size' => $this->openAiSize($aspectRatio), 'quality' => 'medium'])
                ->generate();

            $mediaId = MediaId::fromString(Str::uuid()->toString());
            $imagesDir = storage_path('app/images');
            if (!File::isDirectory($imagesDir)) {
                File::makeDirectory($imagesDir, 0755, true);
            }
            $imagePath = $imagesDir . '/' . $mediaId->value() . '.png';

            $this->saveImage($response->firstImage(), $imagePath);
            $this->resizeToVideoResolution($imagePath, $aspectRatio);

            $media = new Media(
                id: $mediaId,
                userId: $avatar->userId(),
                type: MediaType::IMAGE,
                path: LocalPath::fromString($imagePath),
                createdAt: $this->clock->now(),
            );
            $this->mediaRepository->save($media);

            DB::table('avatar_media')->insert([
                'avatar_id' => $avatar->id()->value(),
                'media_id' => $mediaId->value(),
                'type' => AvatarMediaType::frameForCategory($category)->value,
                'aspect_ratio' => $aspectRatio->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $imagePath;
        } catch (\Throwable $e) {
            Log::error('Failed to generate avatar context frame: ' . $e->getMessage());

            return null;
        }
    }

    private function cachedFramePath(Avatar $avatar, VideoCategory $category, AspectRatio $aspectRatio): ?string
    {
        $row = DB::table('avatar_media')
            ->where('avatar_id', $avatar->id()->value())
            ->where('type', AvatarMediaType::frameForCategory($category)->value)
            ->where('aspect_ratio', $aspectRatio->value)
            ->first();

        if ($row === null) {
            return null;
        }

        $media = $this->mediaRepository->findById(MediaId::fromString($row->media_id));
        $path = $media->path()->value();

        return File::exists($path) ? $path : null;
    }

    private function buildPrompt(Avatar $avatar, VideoCategory $category): string
    {
        $configKey = match ($category) {
            VideoCategory::GAMING => 'prompts.video.context_frame_gaming.prompt',
            VideoCategory::METEOROLOGY => 'prompts.video.context_frame_meteorology.prompt',
        };

        $prompt = (string) config($configKey);

        return str_replace(
            ['{avatar_name}', '{biography}', '{presentation_style}', '{avatar_description}'],
            [
                $avatar->name()->value(),
                $avatar->biography()->value(),
                $avatar->presentationStyle()->value,
                $avatar->description()?->value() ?? '',
            ],
            $prompt,
        );
    }

    private function saveImage(object $image, string $savePath): void
    {
        if (!empty($image->base64)) {
            $data = base64_decode($image->base64, true);
            if ($data === false) {
                throw new \RuntimeException('Failed to decode base64 image data');
            }
            File::put($savePath, $data);

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

    private function openAiSize(AspectRatio $aspectRatio): string
    {
        return match ($aspectRatio) {
            AspectRatio::LANDSCAPE => '1536x1024',
            AspectRatio::PORTRAIT => '1024x1536',
        };
    }

    private function resizeToVideoResolution(string $imagePath, AspectRatio $aspectRatio): void
    {
        [$width, $height] = match ($aspectRatio) {
            AspectRatio::LANDSCAPE => [1280, 720],
            AspectRatio::PORTRAIT => [720, 1280],
        };

        SpatieImage::load($imagePath)
            ->fit(Fit::Crop, $width, $height)
            ->save($imagePath);
    }
}
