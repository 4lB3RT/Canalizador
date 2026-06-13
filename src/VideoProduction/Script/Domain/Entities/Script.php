<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Domain\Entities;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptContent;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;

final class Script
{
    public function __construct(
        private readonly ScriptId $id,
        private ScriptContent $content,
        private readonly ?IntegerId $userId = null,
        private readonly ?VideoCategory $category = null,
        private ?string $title = null,
        private readonly ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
        private readonly Language $language = Language::SPANISH,
    ) {
    }

    public function id(): ScriptId
    {
        return $this->id;
    }

    public function content(): ScriptContent
    {
        return $this->content;
    }

    public function userId(): ?IntegerId
    {
        return $this->userId;
    }

    public function category(): ?VideoCategory
    {
        return $this->category;
    }

    public function language(): Language
    {
        return $this->language;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function createdAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function updateContent(ScriptContent $content): void
    {
        $this->content = $content;
    }

    public function updateTitle(string $title): void
    {
        $this->title = $title;
    }

    public function touch(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id->value(),
            'user_id'    => $this->userId?->value(),
            'category'   => $this->category?->value,
            'language'   => $this->language->value,
            'title'      => $this->title,
            'content'    => $this->content->value(),
            'created_at' => $this->createdAt?->value()->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
