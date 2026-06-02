<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Application\UseCases\GenerateShort;

use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\ValueObjects\Id;
use Helmreel\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;

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

        $this->eventBus->publish(...$short->releaseEvents());
    }
}
