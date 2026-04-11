<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GenerateShorts;

use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;

final readonly class GenerateShorts
{
    public function __construct(
        private YouTubeVideoBuilder   $videoBuilder,
        private VideoRepository       $videoRepository,
        private VideoPublisherFactory $videoPublisherFactory,
    ) {
    }

    /**
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     * @throws VideoFragmentationFailed
     */
    public function execute(GenerateShortsRequest $request): GenerateShortsResponse
    {
        $this->videoBuilder
            ->fromYouTubeId($request->videoYoutubeId)
            ->withDownload()
            ->withAudio()
            ->withTranscription();

        $shorts = $this->videoBuilder
            ->withSegmentDuration(60)
            ->buildShorts();

        dd($shorts);

        $publisher = $this->videoPublisherFactory->create('youtube');
        $publishedIds = [];

        /* @var Video $short */
        foreach ($shorts as $short) {
            $publisher->publish($short);
            $this->videoRepository->save($short);
            $publishedIds[] = $short->platformId()->value();
        }

        return new GenerateShortsResponse(publishedShortIds: $publishedIds);
    }
}
