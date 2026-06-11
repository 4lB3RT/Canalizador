<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\Entities;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

final class Avatar
{
    /**
     * @param AvatarMedia[] $media
     */
    public function __construct(
        private readonly AvatarId $id,
        private readonly IntegerId $userId,
        private ?VoiceId $voiceId,
        private AvatarName $name,
        private LocalPath $profileImagePath,
        private readonly DateTime $createdAt,
        private Biography $biography,
        private PresentationStyle $presentationStyle,
        private Category $category,
        private AvatarDescription $description,
        private array $media = [],
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

    /**
     * @return AvatarMedia[]
     */
    public function media(): array
    {
        return $this->media;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function updateName(AvatarName $name): void
    {
        $this->name = $name;
        $this->updateTimestamp();
    }

    public function updateProfileImagePath(LocalPath $profileImagePath): void
    {
        $this->profileImagePath = $profileImagePath;
        $this->updateTimestamp();
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

    public function clearVoiceId(): void
    {
        $this->voiceId = null;
        $this->updateTimestamp();
    }

    /**
     * @param AvatarMedia[] $media
     */
    public function updateMedia(array $media): void
    {
        $this->media = $media;
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
            'biography' => $this->biography->value(),
            'presentation_style' => $this->presentationStyle->value,
            'category' => $this->category->value,
            'description' => $this->description->value(),
            'images' => array_map(fn (AvatarMedia $m) => $m->toArray(), $this->media),
            'voice_id' => $this->voiceId?->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}

