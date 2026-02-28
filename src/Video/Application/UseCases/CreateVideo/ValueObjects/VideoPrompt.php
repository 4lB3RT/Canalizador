<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\CreateVideo\ValueObjects;

use Canalizador\Avatar\Domain\Entities\Avatar;

final readonly class VideoPrompt
{
    /** @param string[] $referenceImagePaths */
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

    public function technicalVideo(): string
    {
        return $this->technicalVideo;
    }

    /** @return string[] */
    public function referenceImagePaths(): array
    {
        return $this->referenceImagePaths;
    }

    public function toPromptString(): string
    {
        $parts = [];

        if ($this->host !== null) {
            $parts[] = $this->formatHostSection();
        }

        $parts[] = $this->technicalVideo;
        $parts[] = $this->formatScriptSection();

        return implode("\n\n", $parts);
    }

    private function formatHostSection(): string
    {
        if ($this->host === null) {
            return '';
        }

        $presentationStyle = $this->host->presentationStyle()->value;
        $biography = $this->host->biography()->value();
        $description = $this->host->description()->value();

        return <<<HOST
=== HOST CHARACTER SPECIFICATIONS ===

PRESENTATION STYLE:
{$presentationStyle}

BIOGRAPHY (for context and character understanding):
{$biography}

VISUAL DESCRIPTION (MUST match exactly in video):
{$description}

CRITICAL REQUIREMENTS:
- The presenter/creator in the video MUST match this exact visual description with 100% accuracy
- ALL physical attributes, facial features, hair, clothing, accessories, and background elements must be accurately represented
- The presenter MUST be photorealistic and realistic-looking (NOT cartoon, animated, stylized, or artistic renderings)
- Use realistic human proportions, natural skin texture, realistic facial features, and lifelike appearance
- Presentation style ({$presentationStyle}) must be reflected in demeanor, expressions, and overall presence
- Maintain visual consistency with every detail from the description above
HOST;
    }

    private function formatScriptSection(): string
    {
        $script = $this->prompt;

        return <<<SCRIPT_CONTENT
=== SCRIPT CONTENT ===

Generate a video based on the following script. The presenter must speak the exact words while matching the visual specifications provided above.

SCRIPT:
{$script}
SCRIPT_CONTENT;
    }
}
