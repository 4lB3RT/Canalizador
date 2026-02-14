<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Infrastructure\Repositories\OpenAI;

use Canalizador\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\Avatar\Domain\ValueObjects\AvatarName;
use Canalizador\Avatar\Domain\ValueObjects\Biography;
use Canalizador\Avatar\Domain\ValueObjects\PresentationStyle;
use Canalizador\Avatar\Infrastructure\Repositories\OpenAI\AvatarMetadataResult;
use Canalizador\Image\Domain\Entities\ImageCollection;
use Canalizador\Image\Domain\Factories\ImageFactory;
use Canalizador\Image\Domain\Repositories\ImageRepository;
use Canalizador\Image\Domain\ValueObjects\ImageId;
use Canalizador\Shared\Domain\Exceptions\InvalidCollectionType;
use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Image;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image as SpatieImage;

final readonly class OpenAiAvatarRepository
{
    private const string TEXT_MODEL = 'gpt-5.2';
    private const string IMAGE_MODEL = 'gpt-image-1.5';
    private const int NUM_IMAGES = 3;

    public function __construct(
        private string $apiKey,
        private ImageFactory $imageFactory,
        private ImageRepository $imageRepository,
        private HttpClient $httpClient
    ) {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured');
        }
    }

    public function generateMetadata(
        LocalPath $imagePath,
        AvatarName $avatarName,
        Biography $biography,
        PresentationStyle $presentationStyle,
        IntegerId $userId
    ): AvatarMetadataResult {
        $description = $this->generateDescription($imagePath->value(), $avatarName, $biography, $presentationStyle);
        $avatarDescription = AvatarDescription::fromString($description);
        $images = $this->generateImages($imagePath, $avatarDescription, $avatarName, $biography, $presentationStyle, $userId);

        return new AvatarMetadataResult($avatarDescription, $images);
    }

    private function generateDescription(
        string $imagePath,
        AvatarName $avatarName,
        Biography $biography,
        PresentationStyle $presentationStyle
    ): string {
        if (!File::exists($imagePath)) {
            throw new \RuntimeException("Image file not found: {$imagePath}");
        }

        $systemPrompt = config('prompts.avatar.description_generator.system_prompt');
        if (empty($systemPrompt)) {
            throw new \RuntimeException('Avatar description prompt is not configured');
        }


        $systemPrompt = str_replace(
            ['{avatar_name}', '{biography}', '{presentation_style}'],
            [$avatarName->value(), $biography->value(), $presentationStyle->value],
            $systemPrompt
        );

        $userPrompt = 'Analyze this image and provide a detailed description of the person shown. Focus on physical appearance, clothing, and any distinctive features that would be important for generating consistent video content.';

        $image = Image::fromLocalPath($imagePath);

        $response = Prism::text()
            ->using(Provider::OpenAI, self::TEXT_MODEL)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($userPrompt, [$image])
            ->withProviderOptions([
                'max_tokens' => 500,
            ])
            ->asText();

        $description = trim($response->text);

        if (empty($description)) {
            throw new \RuntimeException('No description generated from OpenAI');
        }

        return $description;
    }

    /* @throws InvalidCollectionType */
    private function generateImages(
        LocalPath $avatarImagePath,
        AvatarDescription $description,
        AvatarName $avatarName,
        Biography $biography,
        PresentationStyle $presentationStyle,
        IntegerId $userId
    ): ImageCollection {
        $systemPrompt = config('prompts.avatar.image_gaming_generator.system_prompt');
        if (empty($systemPrompt)) {
            throw new \RuntimeException('Avatar image gaming generator prompt is not configured');
        }

        $basePrompt = str_replace(
            ['{avatar_description}', '{avatar_name}', '{biography}', '{presentation_style}'],
            [$description->value(), $avatarName->value(), $biography->value(), $presentationStyle->value],
            $systemPrompt
        );

        $imagesDir = storage_path('app/images');

        $referenceImage = Image::fromLocalPath($avatarImagePath->value());

        $generatedImages = [];

        $angleVariations = [
            'front view, facing the camera directly, centered composition',
            '30 degree angle from the left side, showing the person and gaming setup',
            '30 degree angle from the right side, showing the person and gaming setup'
        ];

        for ($i = 0; $i < self::NUM_IMAGES; $i++) {
            try {
                $prompt = $basePrompt . ' Camera angle: ' . $angleVariations[$i] . '. Photorealistic, professional photography quality.';

                $response = Prism::image()
                    ->using(Provider::OpenAI, self::IMAGE_MODEL)
                    ->withPrompt($prompt, [$referenceImage])
                    ->withProviderOptions([
                        'size' => '1536x1024',
                        'quality' => 'medium'
                    ])
                    ->generate();

                $imageId = ImageId::fromString(Str::uuid()->toString());
                $imagePath = $imagesDir . '/' . $imageId->value() . '.png';

                $this->saveGeneratedImage($response->firstImage(), $imagePath);
                $this->resizeImageToVideoResolution($imagePath);

                $image = $this->imageFactory->create(
                    id: $imageId,
                    userId: $userId,
                    path: LocalPath::fromString($imagePath)
                );

                $this->imageRepository->save($image);
                $generatedImages[] = $image;
            } catch (\Exception $e) {
                \Log::error("Failed to generate image {$i} for avatar: " . $e->getMessage());
                continue;
            }
        }

        return new ImageCollection($generatedImages);
    }

    private function saveGeneratedImage(object $image, string $savePath): void
    {
        if (!empty($image->base64)) {
            $this->saveBase64Image($image->base64, $savePath);
            return;
        }

        if (!empty($image->url)) {
            $this->downloadImage($image->url, $savePath);
            return;
        }

        throw new \RuntimeException('No image data received from OpenAI (neither base64 nor URL)');
    }

    private function saveBase64Image(string $base64, string $savePath): void
    {
        $imageData = base64_decode($base64, true);

        if ($imageData === false) {
            throw new \RuntimeException('Failed to decode base64 image data');
        }

        File::put($savePath, $imageData);
    }

    private function downloadImage(string $url, string $savePath): void
    {
        $response = $this->httpClient->get($url, [], 60);
        $imageData = $response->body();

        if (empty($imageData)) {
            throw new \RuntimeException('Empty image data received from URL');
        }

        File::put($savePath, $imageData);
    }

    private function resizeImageToVideoResolution(string $imagePath): void
    {
        $resolution = config('sora.resolution', '1280x720');
        [$width, $height] = array_map('intval', explode('x', $resolution));

        SpatieImage::load($imagePath)
            ->fit(Fit::Contain, $width, $height)
            ->resizeCanvas($width, $height, AlignPosition::Center, false, '#000000')
            ->save($imagePath);
    }
}

