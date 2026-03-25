<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\Repositories\SmartVideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;

final readonly class SmartFragmentAndPublishVideo
{
    public function __construct(
        private AudioExtractor       $audioExtractor,
        private VideoTranscriber     $videoTranscriber,
        private SmartVideoFragmenter $smartVideoFragmenter,
        private VideoPublisherFactory $videoPublisherFactory,
    ) {
    }

    /**
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     */
    public function execute(SmartFragmentAndPublishVideoRequest $request): SmartFragmentAndPublishVideoResponse
    {
        $videoPath     = new LocalPath($request->localPath);
        $audioPath     = $this->audioExtractor->extract($videoPath);
        $transcription = $this->videoTranscriber->transcribe($audioPath);
        $fragments     = $this->smartVideoFragmenter->fragment($videoPath, $transcription);

        $publisher        = $this->videoPublisherFactory->create('youtube');
        $publishedVideoIds = [];

        foreach ($fragments as $index => $fragmentPath) {
            $videoToPublish = new VideoToPublish(
                localPath:   $fragmentPath,
                title:       "{$request->baseTitle} - Short " . ($index + 1),
                description: $request->baseDescription,
            );

            $publishedVideoIds[] = $publisher->publish($videoToPublish);
        }

        return new SmartFragmentAndPublishVideoResponse(
            publishedVideoIds: $publishedVideoIds,
        );
    }
}
