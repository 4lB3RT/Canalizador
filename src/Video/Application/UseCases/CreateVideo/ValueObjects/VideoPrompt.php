<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\CreateVideo\ValueObjects;

use Canalizador\Avatar\Domain\Entities\Avatar;

final readonly class VideoPrompt
{
    public function __construct(
        private string $prompt,
        private string $technicalVideo,
        private ?Avatar $host = null,
        private array $referenceImagePaths = [],
    ) {
    }

    public function prompt(): string
    {
        return $this->prompt;
    }

    public function host(): ?Avatar
    {
        return $this->host;
    }

    /** @return string[] */
    public function referenceImagePaths(): array
    {
        return $this->referenceImagePaths;
    }

    public function toPromptString(): string
    {
        $parts = [];

        $prompt = json_decode($this->prompt, true);

        $parts[] = $this->technicalVideo;
        $parts[] =  $prompt['clip_prompts'][0] ?? $this->prompt;

        return implode("\n\n", $parts);
    }
}
