<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\UseCases\GenerateClip;

use Canalizador\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipGenerated;
use Canalizador\VideoProduction\Clip\Domain\Exceptions\ClipNotFound;
use Canalizador\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\ValueObjects\VideoPrompt;
use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoExtender;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoGenerator;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Resolution;

final readonly class GenerateClip
{
    public function __construct(
        private ClipRepository $clipRepository,
        private VideoRepository $videoRepository,
        private VideoGenerator $videoGenerator,
        private VideoExtender $videoExtender,
        private VideoPromptExtractor $videoPromptExtractor,
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
            $lastCompleted = $clips->lastCompleted();

            if ($lastCompleted === null || $lastCompleted->videoUri() === null) {
                throw VideoGenerationFailed::apiError(
                    'Cannot extend: no completed clip with video URI found'
                );
            }

            $clipPrompt = $clip->script()
                ?? 'Continue the video naturally maintaining visual continuity.';

            $generationId = $this->videoExtender->extend($lastCompleted->videoUri(), $clipPrompt);
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
                $video->category()
            );


        return $this->videoGenerator->generate($videoPrompt, Resolution::HD);
    }
}
