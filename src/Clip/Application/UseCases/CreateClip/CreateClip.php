<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\CreateClip;

use Canalizador\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\Clip\Domain\Entities\Clip;
use Canalizador\Clip\Domain\Entities\ClipCollection;
use Canalizador\Clip\Domain\Events\AllClipsCompleted;
use Canalizador\Clip\Domain\Events\ClipCreated;
use Canalizador\Clip\Domain\ValueObjects\ClipStatus;
use Canalizador\Clip\Domain\Factories\ClipFactory;
use Canalizador\Clip\Domain\Repositories\ClipRepository;
use Canalizador\Clip\Domain\ValueObjects\ClipId;
use Canalizador\Clip\Domain\ValueObjects\Sequence;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoExtender;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class CreateClip
{
    public function __construct(
        private VideoRepository $videoRepository,
        private ClipRepository $clipRepository,
        private ClipFactory $clipFactory,
        private VideoGenerator $videoGenerator,
        private VideoExtender $videoExtender,
        private VideoPromptExtractor $videoPromptExtractor,
        private AvatarRepository $avatarRepository,
        private EventBus $eventBus,
        private Clock $clock,
        private int $totalClips = Clip::TOTAL_CLIPS,
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

        $completedCount = $this->countCompleted($clips);

        if ($completedCount >= $this->totalClips) {
            $this->eventBus->publish(
                new AllClipsCompleted($videoId->value(), $this->clock->now())
            );
            return;
        }

        $nextSequence = $completedCount + 1;

        $existingClip = $clips->findGeneratingBySequence($nextSequence);

        if ($existingClip !== null) {
            if ($existingClip->generationId()->isPending()) {
                $generationId = $this->resolveGenerationId($video, $clips, $nextSequence);
                $existingClip->updateGenerationId(GenerationId::fromString($generationId));
                $this->clipRepository->save($existingClip);
            }

            $this->eventBus->publish(
                new ClipCreated($existingClip->id()->value(), $videoId->value(), $this->clock->now())
            );
            return;
        }

        if ($this->hasGeneratingClip($clips)) {
            return;
        }

        $clip = $this->clipFactory->create(
            id: ClipId::fromString($this->generateClipId()),
            videoId: $videoId,
            sequence: Sequence::fromInt($nextSequence),
            generationId: GenerationId::pending(),
        );

        $this->clipRepository->save($clip);

        $generationId = $this->resolveGenerationId($video, $clips, $nextSequence);
        $clip->updateGenerationId(GenerationId::fromString($generationId));
        $this->clipRepository->save($clip);

        $this->eventBus->publish(
            new ClipCreated($clip->id()->value(), $videoId->value(), $this->clock->now())
        );
    }

    private function resolveGenerationId(
        Video $video,
        ClipCollection $clips,
        int $nextSequence,
    ): string {
        if ($nextSequence === 1) {
            return $this->generateFirstClip($video);
        }

        $lastCompleted = $clips->lastCompleted();

        if ($lastCompleted === null || $lastCompleted->videoUri() === null) {
            throw VideoGenerationFailed::apiError(
                'Cannot extend: no completed clip with video URI found'
            );
        }

        return $this->videoExtender->extend($lastCompleted->videoUri());
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

    private function hasGeneratingClip(ClipCollection $clips): bool
    {
        foreach ($clips->items() as $clip) {
            if ($clip->status() === ClipStatus::GENERATING) {
                return true;
            }
        }

        return false;
    }

    private function countCompleted(ClipCollection $clips): int
    {
        return count(array_filter(
            $clips->items(),
            fn (Clip $clip) => $clip->isCompleted()
        ));
    }

    private function generateClipId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }
}
