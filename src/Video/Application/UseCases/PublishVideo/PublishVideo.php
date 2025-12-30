<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\PublishVideo;

use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Exceptions\VideoLocalPathNotSet;
use Canalizador\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;

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
     * @throws VideoGenerationFailed
     * @throws VideoNotFound
     * @throws VideoLocalPathNotSet
     */
    public function execute(PublishVideoRequest $request): PublishVideoResponse
    {
        $videoId = new VideoId($request->videoId);
        $video = $this->videoRepository->findById($videoId);

        if ($video->videoLocalPath() === null) {
            throw VideoLocalPathNotSet::forVideoId($request->videoId);
        }

        $videoPublisher = $this->videoPublisherFactory->create($request->platform);

        $platformVideoId = $videoPublisher->publish(
            video: $video
        );

        $platformUrl = $this->buildPlatformUrl($request->platform, $platformVideoId);

        return new PublishVideoResponse(
            platformVideoId: $platformVideoId,
            platformUrl: $platformUrl,
            platform: $request->platform
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
