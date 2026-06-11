<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\GetVideoFile;

use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;
use RuntimeException;

final readonly class GetVideoFile
{
    public function __construct(
        private VideoRepository $videoRepository,
    ) {
    }

    /**
     * @throws VideoNotFound
     */
    public function execute(string $videoId, int $userId): string
    {
        $video = $this->videoRepository->findById(VideoId::fromString($videoId));

        if ($video->userId()->value() !== $userId) {
            throw VideoNotFound::withId($videoId);
        }

        $path = $video->videoLocalPath()?->value();
        if ($path === null) {
            throw new RuntimeException("Video '{$videoId}' is not ready yet.");
        }

        return $path;
    }
}
