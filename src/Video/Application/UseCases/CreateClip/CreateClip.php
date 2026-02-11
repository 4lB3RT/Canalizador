<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\CreateClip;

use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Video\Domain\Entities\Clip;
use Canalizador\Video\Domain\Events\AllClipsCompleted;
use Canalizador\Video\Domain\Events\ClipCreated;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Factories\ClipFactory;
use Canalizador\Video\Domain\Repositories\ClipRepository;
use Canalizador\Video\Domain\Repositories\VideoExtender;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\ClipId;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Sequence;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class CreateClip
{
    public function __construct(
        private VideoRepository $videoRepository,
        private ClipRepository $clipRepository,
        private ClipFactory $clipFactory,
        private VideoExtender $videoExtender,
        private EventBus $eventBus,
        private Clock $clock,
    ) {
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function execute(CreateClipRequest $request): void
    {
        $videoId = VideoId::fromString($request->videoId);
        $video = $this->videoRepository->findById($videoId);
        $clips = $this->clipRepository->findByVideoId($videoId);

        if ($clips->count() >= Clip::TOTAL_CLIPS) {
            $this->eventBus->publish(
                new AllClipsCompleted($videoId->value(), $this->clock->now())
            );
            return;
        }

        $nextSequence = $clips->count() + 1;

        $generationId = $this->resolveGenerationId($video, $clips, $nextSequence);

        $clip = $this->clipFactory->create(
            id: ClipId::fromString($this->generateClipId()),
            videoId: $videoId,
            sequence: Sequence::fromInt($nextSequence),
            generationId: GenerationId::fromString($generationId),
        );

        $this->clipRepository->save($clip);

        $this->eventBus->publish(
            new ClipCreated($clip->id()->value(), $videoId->value(), $this->clock->now())
        );
    }

    private function resolveGenerationId(
        \Canalizador\Video\Domain\Entities\Video $video,
        \Canalizador\Video\Domain\Entities\ClipCollection $clips,
        int $nextSequence,
    ): string {
        if ($nextSequence === 1) {
            return $video->generationId()->value();
        }

        $lastCompleted = $clips->lastCompleted();

        if ($lastCompleted === null || $lastCompleted->videoUri() === null) {
            throw VideoGenerationFailed::apiError(
                'Cannot extend: no completed clip with video URI found'
            );
        }

        return $this->videoExtender->extend($lastCompleted->videoUri());
    }

    private function generateClipId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }
}
