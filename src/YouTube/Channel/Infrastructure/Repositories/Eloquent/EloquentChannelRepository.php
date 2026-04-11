<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Channel\Infrastructure\Repositories\Eloquent;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\Entities\ChannelCollection;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Canalizador\YouTube\Channel\Infrastructure\DataTransformers\ChannelDataTransformer;

final class EloquentChannelRepository implements ChannelRepository
{
    public function save(Channel $channel): void
    {
        ChannelDAO::updateOrCreate(
            ['id' => $channel->id()->value()],
            [
                'user_id'          => $channel->userId()->value(),
                'title'            => $channel->title()->value(),
                'description'      => $channel->description()->value(),
                'custom_url'       => $channel->customUrl()?->value(),
                'published_at'     => $channel->publishedAt()->value(),
                'thumbnail_url'    => $channel->thumbnailUrl()?->value(),
                'country'          => $channel->country()->value(),
                'view_count'       => $channel->viewCount(),
                'subscriber_count' => $channel->subscriberCount(),
                'video_count'      => $channel->videoCount(),
                'privacy_status'   => $channel->privacyStatus()->value,
                'channel_brand'    => $channel->channelBrand()->value(),
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
        return ChannelDataTransformer::fromArray([
            'id'               => $model->id,
            'user_id'          => $model->user_id,
            'title'            => $model->title,
            'description'      => $model->description,
            'published_at'     => $model->published_at->format('Y-m-d H:i:s'),
            'view_count'       => $model->view_count,
            'subscriber_count' => $model->subscriber_count,
            'video_count'      => $model->video_count,
            'privacy_status'   => $model->privacy_status,
            'country'          => $model->country,
            'channel_brand'    => $model->channel_brand,
            'custom_url'       => $model->custom_url,
            'thumbnail_url'    => $model->thumbnail_url,
        ]);
    }
}
