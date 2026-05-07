<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Application\UseCases\GenerateShort;

use Canalizador\Shared\Shared\Domain\Events\EventBus;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;

final readonly class GenerateShort
{
    private const int SEGMENT_SECONDS = 60;

    public function __construct(
        private YouTubeVideoBuilder $videoBuilder,
        private VideoRepository     $videoRepository,
        private VideoFragmenter     $videoFragmenter,
        private EventBus            $eventBus,
    ) {
    }

    /**
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     */
    public function execute(GenerateShortRequest $request): void
    {
        $this->videoBuilder
            ->fromId(Id::fromString($request->videoId))
            ->withDownload()
            ->withAudio()
            ->withTranscription();

        $parent = $this->videoBuilder->build();

        $existingCount = $parent->shorts()->count();
        $maxShorts     = $parent->maxShorts(self::SEGMENT_SECONDS);

        if ($existingCount >= $maxShorts) {
            return;
        }

        $startSeconds = $existingCount * self::SEGMENT_SECONDS;
        $fragmentPath = $this->videoFragmenter->fragmentAt($parent->videoLocalPath(), $startSeconds, self::SEGMENT_SECONDS);

        $this->videoBuilder
            ->fromFragment($parent, $fragmentPath, $existingCount)
            ->withSubtitles();

        $short = $this->videoBuilder->build();

        $parent->addShort($short);
        $this->videoRepository->save($short);

        dd(1);
        $this->eventBus->publish(...$short->releaseEvents());
    }
}
