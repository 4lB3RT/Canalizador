<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Services;

use Canalizador\Avatar\Domain\Entities\Avatar;
use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;

final readonly class JsonVideoPromptExtractor implements VideoPromptExtractor
{
    public function extractWithAvatar(Script $script, Avatar $avatar, VideoCategory $category): VideoPrompt
    {
        $content = $script->content()->value();
        $scriptData = json_decode($content, true);

        $videoPrompt = $scriptData['full_script'];

        $technicalVideo = $this->getTechnicalVideoPrompt($category);
        $referenceImagePaths = $this->getReferenceImagePaths($category);

        return new VideoPrompt(
            prompt: $videoPrompt,
            technicalVideo: $technicalVideo,
            host: $avatar,
            referenceImagePaths: $referenceImagePaths,
        );
    }

    public function extract(Script $script, VideoCategory $category): VideoPrompt
    {
        $content = $script->content()->value();
        $scriptData = json_decode($content, true);

        $videoPrompt = $scriptData['full_script'];

        $technicalVideo = $this->getTechnicalVideoPrompt($category);
        $referenceImagePaths = $this->getReferenceImagePaths($category);

        return new VideoPrompt(
            prompt: $videoPrompt,
            technicalVideo: $technicalVideo,
            host: null,
            referenceImagePaths: $referenceImagePaths,
        );
    }

    private function getTechnicalVideoPrompt(VideoCategory $category): string
    {
        return match ($category) {
            VideoCategory::GAMING => config('prompts.video.talking_head.system_prompt'),
            VideoCategory::METEOROLOGY => config('prompts.video.talking_head.system_prompt'),
        };
    }

    /** @return string[] */
    private function getReferenceImagePaths(VideoCategory $category): array
    {
        return match ($category) {
            VideoCategory::METEOROLOGY => array_filter([config('weather.map_image_path')]),
            default => [],
        };
    }
}
