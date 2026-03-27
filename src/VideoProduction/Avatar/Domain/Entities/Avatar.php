<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\Entities;

use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Canalizador\VideoProduction\Image\Domain\Entities\ImageCollection;
use Canalizador\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

final class Avatar
{
    public function __construct(
        private readonly AvatarId $id,
        private readonly IntegerId $userId,
        private ?VoiceId $voiceId,
        private readonly AvatarName $name,
        private readonly LocalPath $profileImagePath,
        private readonly DateTime $createdAt,
        private Biography $biography,
        private PresentationStyle $presentationStyle,
        private Category $category,
        private AvatarDescription $description,
        private ImageCollection $images = new ImageCollection([]),
        private ?DateTime $updatedAt = null,
        private readonly ?Clock $clock = null,
    ) {
    }

    public function id(): AvatarId
    {
        return $this->id;
    }

    public function userId(): IntegerId
    {
        return $this->userId;
    }

    public function name(): AvatarName
    {
        return $this->name;
    }

    public function profileImagePath(): LocalPath
    {
        return $this->profileImagePath;
    }

    public function biography(): Biography
    {
        return $this->biography;
    }

    public function presentationStyle(): PresentationStyle
    {
        return $this->presentationStyle;
    }

    public function category(): Category
    {
        return $this->category;
    }

    public function description(): AvatarDescription
    {
        return $this->description;
    }

    public function images(): ImageCollection
    {
        return $this->images;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function updateBiography(Biography $biography): void
    {
        $this->biography = $biography;
        $this->updateTimestamp();
    }

    public function updatePresentationStyle(PresentationStyle $presentationStyle): void
    {
        $this->presentationStyle = $presentationStyle;
        $this->updateTimestamp();
    }

    public function updateCategory(Category $category): void
    {
        $this->category = $category;
        $this->updateTimestamp();
    }

    public function updateDescription(AvatarDescription $description): void
    {
        $this->description = $description;
        $this->updateTimestamp();
    }

    public function voiceId(): ?VoiceId
    {
        return $this->voiceId;
    }

    public function updateVoiceId(VoiceId $voiceId): void
    {
        $this->voiceId = $voiceId;
        $this->updateTimestamp();
    }

    public function updateImages(ImageCollection $images): void
    {
        $this->images = $images;
        $this->updateTimestamp();
    }

    private function updateTimestamp(): void
    {
        if ($this->clock !== null) {
            $this->updatedAt = $this->clock->now();
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'user_id' => $this->userId->value(),
            'name' => $this->name->value(),
            'profile_image_path' => $this->profileImagePath->value(),
            'biography' => $this->biography->value(),
            'presentation_style' => $this->presentationStyle->value,
            'category' => $this->category->value,
            'description' => $this->description->value(),
            'images' => array_map(fn ($image) => $image->toArray(), $this->images->items()),
            'voice_id' => $this->voiceId?->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}

