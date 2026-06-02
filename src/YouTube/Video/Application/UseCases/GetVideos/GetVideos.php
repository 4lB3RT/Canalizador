<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\GetVideos;

use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;

final readonly class GetVideos
{
    public function __construct(
        private VideoRepository $videoRepository,
    ) {
    }

    public function execute(GetVideosRequest $request): GetVideosResponse
    {
        $videos = $this->videoRepository->findByUserId(
            $request->userId,
            $request->category,
            $request->channelId,
            $request->search,
            $request->pagination,
        );
        $total = $this->videoRepository->countByUserId(
            $request->userId,
            $request->category,
            $request->channelId,
            $request->search,
        );

        return new GetVideosResponse(
            videos:     $videos,
            total:      $total,
            pagination: $request->pagination,
        );
    }
}
