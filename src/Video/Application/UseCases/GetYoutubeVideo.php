<?php

declare(strict_types = 1);

namespace Src\Video\Application\UseCases;

use Src\Video\Domain\Entities\Video;
use Src\Video\Domain\Repositories\VideoRepository;
use Src\Video\Domain\ValueObjects\VideoId;

final class GetYoutubeVideo
{
    public function __construct(private VideoRepository $videoRepository)
    {
    }

    public function get(VideoId $videoId): ?Video
    {
        return $this->videoRepository->findById($videoId);
    }
}
