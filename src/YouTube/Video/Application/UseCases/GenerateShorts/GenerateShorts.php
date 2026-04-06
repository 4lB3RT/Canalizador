<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GenerateShorts;

use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use Throwable;

final readonly class GenerateShorts
{
    public function __construct(
        private YouTubeVideoBuilder   $videoBuilder,
        private VideoRepository       $videoRepository,
        private VideoPublisherFactory $videoPublisherFactory,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws Throwable
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     * @throws VideoFragmentationFailed
     * @throws DateMalformedIntervalStringException
     */
    public function execute(GenerateShortsRequest $request): GenerateShortsResponse
    {
        $video = $this->videoBuilder
            ->fromYouTubeId($request->videoYoutubeId)
            ->withDownload()
            ->withAudio()
            ->withTranscription()
            ->build();

        $this->videoRepository->save($video);

        $shorts = $this->videoBuilder
            ->withSegmentDuration(30)
            ->withMaxFragments(1)
            ->buildShorts();

        $publisher = $this->videoPublisherFactory->create('youtube');
        $publishedIds = [];

        /* @var Video $short */
        foreach ($shorts as $short) {
            $publishedId = $publisher->publish($short);
            $short->updatePlatformId(PlatformId::fromString($publishedId));
            $this->videoRepository->save($short);
            $publishedIds[] = $publishedId;
        }

        return new GenerateShortsResponse(publishedShortIds: $publishedIds);
    }
}
