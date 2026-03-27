<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use Canalizador\Shared\Shared\Domain\ValueObjects\Duration;
use Canalizador\Shared\Shared\Domain\ValueObjects\Title;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PublishedAt;
use Canalizador\YouTube\Video\Domain\ValueObjects\Url;
use DateInterval;

final readonly class YoutubeVideoRepository implements VideoRepository
{
    public function __construct(
        private YoutubeDataApiClient $youtubeClient,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws \Throwable
     */
    public function findById(Id $id): Video
    {
        $data = $this->youtubeClient->getVideoById($id->value());

        if (!$data) {
            throw VideoNotFound::withId($id->value());
        }

        $durationMinutes = 0;
        if (isset($data['contentDetails']['duration'])) {
            $interval = new DateInterval($data['contentDetails']['duration']);
            $totalSeconds = $interval->h * 3600 + $interval->i * 60 + $interval->s;
            $durationMinutes = (int)ceil($totalSeconds / 60);
        }

        return new Video(
            id:          $id,
            title:       Title::fromString($data['snippet']['title']),
            publishedAt: PublishedAt::fromString($data['snippet']['publishedAt']),
            duration: new Duration($durationMinutes),
            url:         Url::fromId($id),
        );
    }

    public function save(Video $video): void
    {
        // TODO: Implement save() method.
    }
}
