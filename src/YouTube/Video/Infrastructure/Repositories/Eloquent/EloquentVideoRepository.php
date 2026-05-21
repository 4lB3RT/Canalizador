<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\Eloquent;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\Shared\Shared\Domain\ValueObjects\Search;
use Canalizador\Shared\Shared\Domain\ValueObjects\Total;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Entities\VideoCollection;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Illuminate\Database\Eloquent\Builder;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;
use Canalizador\YouTube\Video\Infrastructure\DAO\VideoDAO;
use Canalizador\YouTube\Video\Infrastructure\DataTransformers\VideoDataTransformer;

final class EloquentVideoRepository implements VideoRepository
{
    public function findById(Id $id): Video
    {
        $model = VideoDAO::with('shorts')->find($id->value());

        if (!$model) {
            throw VideoNotFound::withId($id->value());
        }

        return $this->toEntity($model);
    }

    public function findByPlatformId(PlatformId $platformId): Video
    {
        $model = VideoDAO::with('shorts')->where('platform_id', $platformId->value())->first();

        if (!$model) {
            throw VideoNotFound::withId($platformId->value());
        }

        return $this->toEntity($model);
    }

    public function findScheduledShortsByChannelId(ChannelId $channelId): VideoCollection
    {
        $models = VideoDAO::with('shorts')
            ->where('channel_id', $channelId->value())
            ->where('category', Category::SHORT->value)
            ->where('status', YouTubeStatus::Scheduled->value)
            ->where('published_at', '>', now())
            ->orderBy('published_at')
            ->get();

        $videos = $models->map(fn (VideoDAO $model) => $this->toEntity($model))->all();

        return new VideoCollection($videos);
    }

    public function findLastByChannelId(ChannelId $channelId, ?Category $category = null): ?PlatformId
    {
        $query = VideoDAO::where('channel_id', $channelId->value())
            ->orderBy('published_at', 'desc');

        if ($category !== null) {
            $query->where('category', $category->value);
        }

        $model = $query->first();

        if (!$model || !$model->platform_id) {
            return null;
        }

        return PlatformId::fromString($model->platform_id);
    }

    public function findByChannelId(
        ChannelId $channelId,
        ?Category $category = null,
        ?Pagination $pagination = null,
    ): VideoCollection {
        $query = VideoDAO::with('shorts')
            ->where('channel_id', $channelId->value())
            ->orderBy('published_at', 'desc')
            ->orderBy('id');

        if ($category !== null) {
            $query->where('category', $category->value);
        }

        if ($pagination !== null) {
            $query->limit($pagination->limit())->offset($pagination->offset());
        }

        $videos = $query->get()->map(fn (VideoDAO $model) => $this->toEntity($model))->all();

        return new VideoCollection($videos);
    }

    public function countByChannelId(ChannelId $channelId, ?Category $category = null): Total
    {
        $query = VideoDAO::where('channel_id', $channelId->value());

        if ($category !== null) {
            $query->where('category', $category->value);
        }

        return Total::fromInt($query->count());
    }

    public function findByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
        ?Pagination $pagination = null,
    ): VideoCollection {
        $query = $this->userVideosQuery($userId, $category, $channelId, $search)
            ->orderBy('youtube_videos.published_at', 'desc')
            ->orderBy('youtube_videos.id')
            ->with('shorts');

        if ($pagination !== null) {
            $query->limit($pagination->limit())->offset($pagination->offset());
        }

        $videos = $query->get()->map(fn (VideoDAO $model) => $this->toEntity($model))->all();

        return new VideoCollection($videos);
    }

    public function countByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
    ): Total {
        return Total::fromInt(
            $this->userVideosQuery($userId, $category, $channelId, $search)->count()
        );
    }

    private function userVideosQuery(
        IntegerId $userId,
        ?Category $category,
        ?ChannelId $channelId,
        ?Search $search,
    ): Builder {
        $query = VideoDAO::query()
            ->select('youtube_videos.*')
            ->join('youtube_channels', 'youtube_channels.id', '=', 'youtube_videos.channel_id')
            ->where('youtube_channels.user_id', $userId->value());

        if ($category !== null) {
            $query->where('youtube_videos.category', $category->value);
        }

        if ($channelId !== null) {
            $query->where('youtube_videos.channel_id', $channelId->value());
        }

        if ($search !== null) {
            $query->where('youtube_videos.title', 'like', '%' . $search->value() . '%');
        }

        return $query;
    }

    public function save(Video $video): void
    {
        $data = $video->toArray();

        VideoDAO::updateOrCreate(
            ['id' => $data['id']],
            [
                'title'               => $data['title'],
                'url'                 => $data['url'],
                'published_at'        => $data['published_at'],
                'local_path'          => $data['video_local_path'],
                'audio_path'          => $data['audio_local_path'],
                'transcription'       => $data['transcription'],
                'channel_id'          => $data['channel_id'],
                'category'            => $data['category'],
                'status'              => $data['status'],
                'duration'            => $data['duration'],
                'description'         => $data['description'] ?? null,
                'platform_id'         => $data['platform_id'] ?? null,
                'parent_id'           => $data['parent_id']   ?? null,
                'published_short_ids' => array_map(
                    static fn (YouTubeVideoId $id) => $id->value(),
                    $video->publishedShortIds()
                ),
            ]
        );
    }

    private function toEntity(VideoDAO $model): Video
    {
        return VideoDataTransformer::fromArray([
            'id'               => $model->id,
            'platform_id'      => $model->platform_id,
            'parent_id'        => $model->parent_id,
            'channel_id'       => $model->channel_id,
            'title'            => $model->title,
            'published_at'     => $model->published_at->format('Y-m-d H:i:s'),
            'metrics'          => [],
            'category'         => $model->category ?? 'video',
            'status'           => $model->status   ?? 'private',
            'url'              => $model->url,
            'video_local_path' => $model->local_path,
            'audio_local_path' => $model->audio_path,
            'transcription'    => $model->transcription,
            'duration'         => $model->duration,
            'description'      => $model->description,
            'shorts'           => $model->shorts->map(fn (VideoDAO $short) => [
                'id'               => $short->id,
                'platform_id'      => $short->platform_id,
                'parent_id'        => $short->parent_id,
                'channel_id'       => $short->channel_id,
                'title'            => $short->title,
                'published_at'     => $short->published_at->format('Y-m-d H:i:s'),
                'metrics'          => [],
                'category'         => $short->category ?? 'short',
                'status'           => $short->status   ?? 'private',
                'url'              => $short->url,
                'video_local_path' => $short->local_path,
                'audio_local_path' => $short->audio_path,
                'transcription'    => $short->transcription,
                'duration'         => $short->duration,
                'description'      => $short->description,
            ])->toArray(),
        ]);
    }
}
