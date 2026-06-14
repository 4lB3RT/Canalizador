<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Services;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Helmreel\VideoProduction\Video\Domain\Services\AvatarContextFrameGenerator;
use Helmreel\VideoProduction\Video\Domain\Services\ScriptTranslator;
use Helmreel\VideoProduction\Video\Domain\Services\VideoPromptExtractor;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\AspectRatio;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoModel;

final readonly class JsonVideoPromptExtractor implements VideoPromptExtractor
{
    public function __construct(
        private GdChromaKeyCompositor $compositor,
        private ScriptTranslator $scriptTranslator,
        private AvatarContextFrameGenerator $avatarContextFrameGenerator,
    ) {
    }

    public function extractWithAvatar(Script $script, Avatar $avatar, VideoCategory $category, Language $voiceLanguage, VideoModel $model, AspectRatio $aspectRatio): VideoPrompt
    {
        $content = $this->resolveContent($script, $voiceLanguage);
        $scriptData = json_decode($content, true);

        $videoPrompt = $scriptData['full_script'];

        $technicalVideo = $this->getTechnicalVideoPrompt($category, $voiceLanguage);
        $referenceImagePaths = $this->getReferenceImagePaths($category);

        $firstFramePath = $this->avatarContextFrameGenerator->frameFor($avatar, $category, $aspectRatio)
            ?? $this->buildFirstFrame($category);

        return new VideoPrompt(
            prompt: $videoPrompt,
            technicalVideo: $technicalVideo,
            host: $avatar,
            referenceImagePaths: $referenceImagePaths,
            firstFramePath: $firstFramePath,
        );
    }

    public function extractForChainedClip(string $clipPrompt, VideoCategory $category, Language $voiceLanguage, string $firstFramePath): VideoPrompt
    {
        return new VideoPrompt(
            prompt: $clipPrompt,
            technicalVideo: $this->getTechnicalVideoPrompt($category, $voiceLanguage),
            host: null,
            referenceImagePaths: $this->getReferenceImagePaths($category),
            firstFramePath: $firstFramePath,
            pinLastFrame: false,
        );
    }

    public function extract(Script $script, VideoCategory $category, Language $voiceLanguage): VideoPrompt
    {
        $content = $this->resolveContent($script, $voiceLanguage);
        $scriptData = json_decode($content, true);

        $videoPrompt = $scriptData['full_script'];

        $technicalVideo = $this->getTechnicalVideoPrompt($category, $voiceLanguage);
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

    private function resolveContent(Script $script, Language $voiceLanguage): string
    {
        $content = $script->content()->value();

        if ($voiceLanguage === $script->language()) {
            return $content;
        }

        return $this->scriptTranslator->translate($content, $voiceLanguage);
    }

    private function getTechnicalVideoPrompt(VideoCategory $category, Language $language): string
    {
        $prompt = match ($category) {
            VideoCategory::GAMING => config('prompts.video.talking_head.system_prompt'),
            VideoCategory::METEOROLOGY => config('prompts.video.technical_meteorology.prompt'),
        };

        return str_replace('{language}', $language->promptLabel(), $prompt);
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
