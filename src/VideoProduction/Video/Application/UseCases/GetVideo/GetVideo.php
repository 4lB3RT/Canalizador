<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\GetVideo;

use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;

final readonly class GetVideo
{
    public function __construct(
        private VideoRepository $videoRepository,
    ) {
    }

    /**
     * @throws VideoNotFound
     */
    public function execute(string $videoId, int $userId): array
    {
        $video = $this->videoRepository->findById(VideoId::fromString($videoId));

        if ($video->userId()->value() !== $userId) {
            throw VideoNotFound::withId($videoId);
        }

        return $video->toArray();
    }
}
