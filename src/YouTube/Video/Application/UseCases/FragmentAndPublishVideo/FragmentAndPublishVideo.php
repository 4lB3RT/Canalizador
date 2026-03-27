<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\FragmentAndPublishVideo;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;

final readonly class FragmentAndPublishVideo
{
    public function __construct(
        private VideoFragmenter $videoFragmenter,
        private VideoPublisherFactory $videoPublisherFactory,
    ) {
    }

    /**
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     */
    public function execute(FragmentAndPublishVideoRequest $request): FragmentAndPublishVideoResponse
    {
        $videoPath = new LocalPath($request->localPath);
        $fragments = $this->videoFragmenter->fragment($videoPath, $request->segmentDurationSeconds);

        $publisher        = $this->videoPublisherFactory->create('youtube');
        $publishedVideoIds = [];

        foreach ($fragments as $index => $fragmentPath) {
            $videoToPublish = new VideoToPublish(
                localPath:   $fragmentPath,
                title:       "{$request->baseTitle} - Part " . ($index + 1),
                description: $request->baseDescription,
            );

            $publishedVideoIds[] = $publisher->publish($videoToPublish);
        }

        return new FragmentAndPublishVideoResponse(
            publishedVideoIds: $publishedVideoIds,
        );
    }
}
