<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Repositories\Youtube;

use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\Entities\ChannelCollection;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelBrand;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Country;
use Canalizador\YouTube\Channel\Domain\ValueObjects\CustomUrl;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Description;
use Canalizador\YouTube\Channel\Domain\ValueObjects\PrivacyStatus;
use Canalizador\YouTube\Channel\Domain\ValueObjects\SubscriberCount;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ThumbnailUrl;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Title;
use Canalizador\YouTube\Channel\Domain\ValueObjects\VideoCount;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ViewCount;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use DateTimeImmutable;
use Google_Service_Exception;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

final readonly class YoutubeChannelRepository implements ChannelRepository
{
    public function __construct(
        private YoutubeDataApiClient $youtubeClient,
    ) {
    }

    /**
     * @throws Google_Service_Exception
     */
    public function save(Channel $channel): void
    {
        $this->youtubeClient->updateChannel($channel);
    }

    /**
     * @param ChannelId $id
     * @return Channel
     * @throws ChannelNotFound
     * @throws Google_Service_Exception
     * @throws \DateMalformedStringException
     */
    public function findById(ChannelId $id): Channel
    {
        $data = $this->youtubeClient->getChannelById($id->value());
        if (!$data) {
            throw ChannelNotFound::withId($id->value());
        }

        $snippet = $data['snippet'] ?? [];
        $statistics = $data['statistics'] ?? [];

        $publishedAt = $snippet['publishedAt'] ?? null;
        $publishedAtDateTime = $publishedAt
            ? new DateTime(new DateTimeImmutable($publishedAt))
            : new DateTime(new DateTimeImmutable());

        $thumbnails = $snippet['thumbnails'] ?? [];
        $thumbnailUrl = $thumbnails['default']['url'] ?? null;

        $title = $snippet['title'] ?? '';
        $description = $snippet['description'] ?? '';

        if (empty($title)) {
            throw new \InvalidArgumentException('Channel title cannot be empty');
        }

        $country = !empty($snippet['country'])
            ? Country::fromString($snippet['country'])
            : Country::fromString('US');

        $channelBrand = ChannelBrand::fromString('YouTube Channel');

        return new Channel(
            id: $id,
            userId: new IntegerId(0),
            title: Title::fromString($title),
            description: Description::fromString($description),
            publishedAt: $publishedAtDateTime,
            viewCount: ViewCount::fromInt((int) ($statistics['viewCount'] ?? 0)),
            subscriberCount: SubscriberCount::fromInt((int) ($statistics['subscriberCount'] ?? 0)),
            videoCount: VideoCount::fromInt((int) ($statistics['videoCount'] ?? 0)),
            privacyStatus: PrivacyStatus::PUBLIC,
            country: $country,
            channelBrand: $channelBrand,
            customUrl: !empty($snippet['customUrl']) ? CustomUrl::fromString($snippet['customUrl']) : null,
            thumbnailUrl: $thumbnailUrl ? ThumbnailUrl::fromString($thumbnailUrl) : null,
        );
    }


    public function findByUserId(IntegerId $userId): ChannelCollection
    {
        throw new \Exception('Not implemented. Use EloquentChannelRepository for user-based queries.');
    }

    public function delete(ChannelId $id): void
    {
        throw new \Exception('Not implemented. Use EloquentChannelRepository for deletion.');
    }
}
