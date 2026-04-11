<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Channel\Infrastructure\Repositories\Youtube;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\Entities\ChannelCollection;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Channel\Infrastructure\DataTransformers\ChannelDataTransformer;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use DateTimeImmutable;
use Exception;
use Google_Service_Exception;

final readonly class YoutubeChannelRepository implements ChannelRepository
{
    public function __construct(
        private YoutubeDataApiClient $youtubeClient,
    ) {
    }

    /**
     * @throws Google_Service_Exception
     * @throws \Google\Service\Exception
     */
    public function save(Channel $channel): void
    {
        $this->youtubeClient->updateChannel($channel);
    }

    /**
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

        $snippet    = $data['snippet']       ?? [];
        $statistics = $data['statistics'] ?? [];

        $title = $snippet['title'] ?? '';
        if (empty($title)) {
            throw new \InvalidArgumentException('Channel title cannot be empty');
        }

        $publishedAt  = $snippet['publishedAt']                     ?? null;
        $thumbnailUrl = ($snippet['thumbnails']['default']['url']) ?? null;

        return ChannelDataTransformer::fromArray([
            'id'           => $id->value(),
            'user_id'      => 0,
            'title'        => $title,
            'description'  => $snippet['description'] ?? '',
            'published_at' => $publishedAt
                ? (new DateTimeImmutable($publishedAt))->format('Y-m-d H:i:s')
                : (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'view_count'       => (int) ($statistics['viewCount'] ?? 0),
            'subscriber_count' => (int) ($statistics['subscriberCount'] ?? 0),
            'video_count'      => (int) ($statistics['videoCount'] ?? 0),
            'privacy_status'   => 'public',
            'country'          => !empty($snippet['country']) ? $snippet['country'] : 'US',
            'channel_brand'    => 'YouTube Channel',
            'custom_url'       => !empty($snippet['customUrl']) ? $snippet['customUrl'] : null,
            'thumbnail_url'    => $thumbnailUrl,
        ]);
    }

    /* @throws Exception */
    public function findByUserId(IntegerId $userId): ChannelCollection
    {
        throw new Exception('Not implemented. Use EloquentChannelRepository for user-based queries.');
    }

    /* @throws Exception */
    public function delete(ChannelId $id): void
    {
        throw new Exception('Not implemented. Use EloquentChannelRepository for deletion.');
    }
}
