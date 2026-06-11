<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\GetVideos;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;

final readonly class GetVideos
{
    public function __construct(
        private VideoRepository $videoRepository,
    ) {
    }

    public function execute(int $userId): array
    {
        $videos = $this->videoRepository->findByUserId(new IntegerId($userId));

        return array_map(fn ($video) => $video->toArray(), $videos);
    }
}
