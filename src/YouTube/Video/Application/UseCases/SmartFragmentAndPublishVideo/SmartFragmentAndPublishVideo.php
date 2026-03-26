<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\Repositories\SmartVideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\LocalPath as VideoLocalPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;

final readonly class SmartFragmentAndPublishVideo
{
    public function __construct(
        private VideoRepository      $videoRepository,
        private AudioExtractor       $audioExtractor,
        private VideoTranscriber     $videoTranscriber,
        private SmartVideoFragmenter $smartVideoFragmenter,
        private VideoPublisherFactory $videoPublisherFactory,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     */
    public function execute(SmartFragmentAndPublishVideoRequest $request): SmartFragmentAndPublishVideoResponse
    {
        $video = $this->videoRepository->findById(new Id($request->videoId));

        $videoPath = new LocalPath($request->localPath);
        $audioPath = $this->audioExtractor->extract($videoPath);

        $video->updateLocalPath(new VideoLocalPath($videoPath->value()));
        $video->updateAudioPath($audioPath);
        $this->videoRepository->save($video);

        $transcription = $this->videoTranscriber->transcribe($audioPath);
        $video->updateTranscription($transcription);
        $this->videoRepository->save($video);

        $fragments = $this->smartVideoFragmenter->fragment($videoPath, $transcription);

        $publisher = $this->videoPublisherFactory->create('youtube');

        foreach ($fragments as $index => $fragmentPath) {
            $videoToPublish = new VideoToPublish(
                localPath:   $fragmentPath,
                title:       "{$request->baseTitle} - Short " . ($index + 1),
                description: $request->baseDescription,
            );

            $publishedId = $publisher->publish($videoToPublish);
            $video->addPublishedShortId($publishedId);
            $this->videoRepository->save($video);
        }

        return new SmartFragmentAndPublishVideoResponse(
            publishedVideoIds: $video->publishedShortIds(),
        );
    }
}
