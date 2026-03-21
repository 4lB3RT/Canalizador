<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GetVideo;

use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;

final readonly class GetVideo
{
    public function __construct(
        private VideoRepository $videoRepository,
    ) {
    }

    /**
     * @throws VideoNotFound
     */
    public function execute(GetVideoRequest $request): GetVideoResponse
    {
        $video = $this->videoRepository->findById(
            Id::fromString($request->videoId)
        );

        return new GetVideoResponse($video);
    }
}
