<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Repositories\Eloquent;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
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
use Canalizador\YouTube\Channel\Infrastructure\DAO\ChannelDAO;

final class EloquentChannelRepository implements ChannelRepository
{
    public function save(Channel $channel): void
    {
        ChannelDAO::updateOrCreate(
            ['id' => $channel->id()->value()],
            [
                'user_id' => $channel->userId()->value(),
                'title' => $channel->title()->value(),
                'description' => $channel->description()->value(),
                'custom_url' => $channel->customUrl()?->value(),
                'published_at' => $channel->publishedAt()->value(),
                'thumbnail_url' => $channel->thumbnailUrl()?->value(),
                'country' => $channel->country()->value(),
                'view_count' => $channel->viewCount()->value(),
                'subscriber_count' => $channel->subscriberCount()->value(),
                'video_count' => $channel->videoCount()->value(),
                'privacy_status' => $channel->privacyStatus()->value,
                'channel_brand' => $channel->channelBrand()->value(),
            ]
        );
    }

    /**
     * @throws ChannelNotFound
     */
    public function findById(ChannelId $id): Channel
    {
        $model = ChannelDAO::find($id->value());

        if (!$model) {
            throw ChannelNotFound::withId($id->value());
        }

        return $this->toEntity($model);
    }

    public function findByUserId(IntegerId $userId): ChannelCollection
    {
        $models = ChannelDAO::where('user_id', $userId->value())->get();

        $channels = [];
        foreach ($models as $model) {
            $channels[] = $this->toEntity($model);
        }

        return new ChannelCollection($channels);
    }

    public function delete(ChannelId $id): void
    {
        ChannelDAO::destroy($id->value());
    }

    private function toEntity(ChannelDAO $model): Channel
    {
        if (!$model->country) {
            throw new \RuntimeException("Channel {$model->id} does not have a country. It must be set before loading.");
        }

        if (!$model->channel_brand) {
            throw new \RuntimeException("Channel {$model->id} does not have a channel brand. It must be set before loading.");
        }

        return new Channel(
            id: ChannelId::fromString($model->id),
            userId: new IntegerId($model->user_id),
            title: Title::fromString($model->title),
            description: Description::fromString($model->description),
            publishedAt: new DateTime($model->published_at->toDateTimeImmutable()),
            viewCount: ViewCount::fromInt($model->view_count),
            subscriberCount: SubscriberCount::fromInt($model->subscriber_count),
            videoCount: VideoCount::fromInt($model->video_count),
            privacyStatus: PrivacyStatus::from($model->privacy_status),
            country: Country::fromString($model->country),
            channelBrand: ChannelBrand::fromString($model->channel_brand),
            customUrl: $model->custom_url ? CustomUrl::fromString($model->custom_url) : null,
            thumbnailUrl: $model->thumbnail_url ? ThumbnailUrl::fromString($model->thumbnail_url) : null,
        );
    }
}

