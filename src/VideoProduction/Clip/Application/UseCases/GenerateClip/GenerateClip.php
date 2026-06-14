<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\UseCases\GenerateClip;

use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Clip\Domain\Entities\Clip;
use Helmreel\VideoProduction\Clip\Domain\Entities\ClipCollection;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipGenerated;
use Helmreel\VideoProduction\Clip\Domain\Exceptions\ClipNotFound;
use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Helmreel\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoExtender;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoGenerator;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\Services\ScriptTranslator;
use Helmreel\VideoProduction\Video\Domain\Services\VideoPromptExtractor;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\GenerationId;

final readonly class GenerateClip
{
    public function __construct(
        private ClipRepository $clipRepository,
        private VideoRepository $videoRepository,
        private VideoGenerator $videoGenerator,
        private VideoExtender $videoExtender,
        private VideoPromptExtractor $videoPromptExtractor,
        private ScriptTranslator $scriptTranslator,
        private AvatarRepository $avatarRepository,
        private EventBus $eventBus,
        private Clock $clock,
    ) {
    }

    /**
     * @param GenerateClipRequest $request
     * @throws VideoGenerationFailed
     * @throws ClipNotFound
     * @throws VideoNotFound
     */
    public function execute(GenerateClipRequest $request): void
    {
        $clip = $this->clipRepository->findById(ClipId::fromString($request->clipId));
        $video = $this->videoRepository->findById($clip->videoId());
        $clips = $this->clipRepository->findByVideoId($clip->videoId());

        if ($clip->sequence()->value() === 1) {
            $generationId = $this->generateFirstClip($video);
        } else {
            $generationId = $this->generateChainedClip($video, $clip, $clips);
        }

        $clip->updateGenerationId(GenerationId::fromString($generationId));
        $this->clipRepository->save($clip);

        $this->eventBus->publish(
            new ClipGenerated($clip->id()->value(), $clip->videoId()->value(), $this->clock->now())
        );
    }

    private function generateFirstClip(Video $video): string
    {
        $videoPrompt = $this->videoPromptExtractor->extractWithAvatar(
                $video->script(),
                $this->avatarRepository->findById($video->avatarId()),
                $video->category(),
                $video->language(),
                $video->model(),
                $video->aspectRatio(),
            );


        return $this->videoGenerator->generate($videoPrompt, $video->resolution(), $video->model(), $video->aspectRatio());
    }

    private function generateChainedClip(Video $video, Clip $clip, ClipCollection $clips): string
    {
        $lastCompleted = $clips->lastCompleted();

        if ($lastCompleted === null || $lastCompleted->lastFramePath() === null) {
            throw VideoGenerationFailed::apiError(
                'Cannot chain clip: no completed clip with a last frame found'
            );
        }

        $clipPrompt = $clip->script()
            ?? 'Continue the video naturally maintaining visual continuity.';

        if ($video->language() !== $video->script()->language()) {
            $clipPrompt = $this->scriptTranslator->translateText($clipPrompt, $video->language());
        }

        $videoPrompt = $this->videoPromptExtractor->extractForChainedClip(
            $clipPrompt,
            $video->category(),
            $video->language(),
            $lastCompleted->lastFramePath()->value(),
        );

        return $this->videoGenerator->generate($videoPrompt, $video->resolution(), $video->model(), $video->aspectRatio());
    }
}
