<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\GenerateClip;

use Canalizador\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\Clip\Domain\Events\ClipGenerated;
use Canalizador\Clip\Domain\Repositories\ClipRepository;
use Canalizador\Clip\Domain\ValueObjects\ClipId;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoExtender;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Domain\ValueObjects\GenerationId;

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
     * @throws VideoGenerationFailed
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

            $generationId = $this->videoExtender->extend($lastCompleted->videoUri());
        }

        $clip->updateGenerationId(GenerationId::fromString($generationId));
        $this->clipRepository->save($clip);

        $this->eventBus->publish(
            new ClipGenerated($clip->id()->value(), $clip->videoId()->value(), $this->clock->now())
        );
    }

    private function generateFirstClip(Video $video): string
    {
        $videoPrompt = $video->avatarId() !== null
            ? $this->videoPromptExtractor->extractWithAvatar(
                $video->script(),
                $this->avatarRepository->findById($video->avatarId()),
                $video->category()
            )
            : $this->videoPromptExtractor->extract($video->script(), $video->category());

        return $this->videoGenerator->generate($videoPrompt);
    }
}
