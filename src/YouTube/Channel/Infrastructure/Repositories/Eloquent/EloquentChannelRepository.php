<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Channel\Infrastructure\Repositories\Eloquent;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;
use Helmreel\Shared\Shared\Domain\ValueObjects\Total;
use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\Entities\ChannelCollection;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Helmreel\YouTube\Channel\Infrastructure\DataTransformers\ChannelDataTransformer;

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
                'auto_sync'        => $channel->autoSync(),
                'auto_publish'     => $channel->autoPublish(),
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

    public function findByUserId(IntegerId $userId, ?Pagination $pagination = null): ChannelCollection
    {
        $query = ChannelDAO::where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->orderBy('id');

        if ($pagination !== null) {
            $query->limit($pagination->limit())->offset($pagination->offset());
        }

        $channels = [];
        foreach ($query->get() as $model) {
            $channels[] = $this->toEntity($model);
        }

        return new ChannelCollection($channels);
    }

    public function countByUserId(IntegerId $userId): Total
    {
        return Total::fromInt(ChannelDAO::where('user_id', $userId->value())->count());
    }

    public function findAllWithAutoSync(): ChannelCollection
    {
        $models = ChannelDAO::where('auto_sync', true)->orderBy('id')->get();

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
            'auto_sync'        => (bool) $model->auto_sync,
            'auto_publish'     => (bool) $model->auto_publish,
            'custom_url'       => $model->custom_url,
            'thumbnail_url'    => $model->thumbnail_url,
        ]);
    }
}
