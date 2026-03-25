<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\PublishVideo;

use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoLocalPathNotSet;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;

final readonly class PublishVideo
{
    private const array PLATFORM_URLS = [
        'youtube' => 'https://www.youtube.com/watch?v=',
    ];

    public function __construct(
        private VideoRepository $videoRepository,
        private VideoPublisherFactory $videoPublisherFactory
    ) {
    }

    /**
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     * @throws VideoLocalPathNotSet
     */
    public function execute(PublishVideoRequest $request): PublishVideoResponse
    {
        $videoId = new VideoId($request->videoId);
        $video   = $this->videoRepository->findById($videoId);

        if ($video->videoLocalPath() === null) {
            throw VideoLocalPathNotSet::forVideoId($request->videoId);
        }

        $videoPublisher = $this->videoPublisherFactory->create($request->platform);

        $videoToPublish = new VideoToPublish(
            localPath:   $video->videoLocalPath(),
            title:       $video->title()->value(),
            description: $video->description()->value(),
        );

        $platformVideoId = $videoPublisher->publish($videoToPublish);

        $platformUrl = $this->buildPlatformUrl($request->platform, $platformVideoId);

        return new PublishVideoResponse(
            platformVideoId: $platformVideoId,
            platformUrl:     $platformUrl,
            platform:        $request->platform
        );
    }

    private function buildPlatformUrl(string $platform, string $platformVideoId): string
    {
        $baseUrl = self::PLATFORM_URLS[$platform] ?? '';

        if ($baseUrl === '') {
            return '';
        }

        return $baseUrl . $platformVideoId;
    }
}
