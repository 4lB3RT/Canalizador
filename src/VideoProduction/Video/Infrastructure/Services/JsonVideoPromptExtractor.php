<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Infrastructure\Services;

use Canalizador\VideoProduction\Avatar\Domain\Entities\Avatar;
use Canalizador\VideoProduction\Script\Domain\Entities\Script;
use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\VideoProduction\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoCategory;

final readonly class JsonVideoPromptExtractor implements VideoPromptExtractor
{
    public function __construct(
        private GdChromaKeyCompositor $compositor,
    ) {
    }

    public function extractWithAvatar(Script $script, Avatar $avatar, VideoCategory $category): VideoPrompt
    {
        $content = $script->content()->value();
        $scriptData = json_decode($content, true);

        $videoPrompt = $scriptData['full_script'];

        $technicalVideo = $this->getTechnicalVideoPrompt($category);
        $referenceImagePaths = $this->getReferenceImagePaths($category);
        $firstFramePath = $this->buildFirstFrame($category);

        return new VideoPrompt(
            prompt: $videoPrompt,
            technicalVideo: $technicalVideo,
            host: $avatar,
            referenceImagePaths: $referenceImagePaths,
            firstFramePath: $firstFramePath,
        );
    }

    public function extract(Script $script, VideoCategory $category): VideoPrompt
    {
        $content = $script->content()->value();
        $scriptData = json_decode($content, true);

        $videoPrompt = $scriptData['full_script'];

        $technicalVideo = $this->getTechnicalVideoPrompt($category);
        $referenceImagePaths = $this->getReferenceImagePaths($category);
        $firstFramePath = $this->buildFirstFrame($category);

        return new VideoPrompt(
            prompt: $videoPrompt,
            technicalVideo: $technicalVideo,
            host: null,
            referenceImagePaths: $referenceImagePaths,
            firstFramePath: $firstFramePath,
        );
    }

    private function getTechnicalVideoPrompt(VideoCategory $category): string
    {
        return match ($category) {
            VideoCategory::GAMING => config('prompts.video.talking_head.system_prompt'),
            VideoCategory::METEOROLOGY => config('prompts.video.technical_meteorology.prompt'),
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

    private function buildFirstFrame(VideoCategory $category): ?string
    {
        if ($category !== VideoCategory::METEOROLOGY) {
            return null;
        }

        $studioPath = config('weather.studio_image_path');
        $mapPath = config('weather.map_image_path');

        if ($studioPath === null || $mapPath === null) {
            return null;
        }

        if (!file_exists($studioPath) || !file_exists($mapPath)) {
            return null;
        }

        $outputPath = storage_path('app/maps/studio_map_composite.png');

        return $this->compositor->composite($studioPath, $mapPath, $outputPath);
    }
}
