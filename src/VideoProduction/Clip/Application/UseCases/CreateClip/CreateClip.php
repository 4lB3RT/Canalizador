<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\UseCases\CreateClip;

use Canalizador\Shared\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Clip\Domain\Entities\Clip;
use Canalizador\VideoProduction\Clip\Domain\Entities\ClipCollection;
use Canalizador\VideoProduction\Clip\Domain\Events\AllClipsCompleted;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipCreated;
use Canalizador\VideoProduction\Clip\Domain\Factories\ClipFactory;
use Canalizador\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipStatus;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\Sequence;
use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;

final readonly class CreateClip
{
    public function __construct(
        private VideoRepository $videoRepository,
        private ClipRepository $clipRepository,
        private ClipFactory $clipFactory,
        private EventBus $eventBus,
        private Clock $clock,
        private int $totalClips = Clip::TOTAL_CLIPS,
    ) {
    }

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
            $this->eventBus->publish(
                new ClipCreated($existingClip->id()->value(), $videoId->value(), $this->clock->now())
            );
            return;
        }

        if ($this->hasGeneratingClip($clips)) {
            return;
        }

        $clipScript = $this->extractClipScript($video, $nextSequence);

        $clip = $this->clipFactory->create(
            id: ClipId::fromString($this->generateClipId()),
            videoId: $videoId,
            sequence: Sequence::fromInt($nextSequence),
            generationId: GenerationId::pending(),
            script: $clipScript,
        );

        $this->clipRepository->save($clip);

        $this->eventBus->publish(
            new ClipCreated($clip->id()->value(), $videoId->value(), $this->clock->now())
        );
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

    private function extractClipScript(Video $video, int $sequence): ?string
    {
        $scriptContent = $video->script()->content()->value();
        $scriptData = json_decode($scriptContent, true);

        if (!is_array($scriptData) || !isset($scriptData['clip_prompts'])) {
            return null;
        }

        return $scriptData['clip_prompts'][$sequence - 1] ?? null;
    }

    private function generateClipId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }
}
