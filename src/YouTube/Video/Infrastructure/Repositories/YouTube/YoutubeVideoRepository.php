<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PublishedAt;
use Canalizador\YouTube\Video\Domain\ValueObjects\Title;
use Canalizador\YouTube\Video\Domain\ValueObjects\Url;

final readonly class YoutubeVideoRepository implements VideoRepository
{
    public function __construct(
        private YoutubeDataApiClient $youtubeClient,
    ) {
    }

    /**
     * @throws VideoNotFound
     */
    public function findById(Id $id): Video
    {
        $data = $this->youtubeClient->getVideoById($id->value());

        if (!$data) {
            throw VideoNotFound::withId($id->value());
        }

        return new Video(
            id:          $id,
            title:       Title::fromString($data['snippet']['title']),
            publishedAt: PublishedAt::fromString($data['snippet']['publishedAt']),
            url:         Url::fromId($id),
        );
    }
}
