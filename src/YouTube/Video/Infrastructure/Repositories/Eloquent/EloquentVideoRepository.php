<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\Eloquent;

use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\PublishedAt;
use Canalizador\YouTube\Video\Domain\ValueObjects\Title;
use Canalizador\YouTube\Video\Domain\ValueObjects\Url;
use Canalizador\YouTube\Video\Infrastructure\DAO\VideoDAO;

final class EloquentVideoRepository implements VideoRepository
{
    /**
     * @throws VideoNotFound
     */
    public function findById(Id $id): Video
    {
        $model = VideoDAO::find($id->value());

        if (!$model) {
            throw VideoNotFound::withId($id->value());
        }

        return $this->toEntity($model);
    }

    public function save(Video $video): void
    {
        VideoDAO::updateOrCreate(
            ['id' => $video->id()->value()],
            [
                'title'               => $video->title()->value(),
                'url'                 => $video->url()->value(),
                'published_at'        => $video->publishedAt()->format('Y-m-d H:i:s'),
                'local_path'          => $video->localPath()?->value(),
                'audio_path'          => $video->audioPath()?->value(),
                'transcription'       => $video->transcription() ?: null,
                'published_short_ids' => $video->publishedShortIds() ?: null,
                'channel_id'          => $video->channelId(),
            ]
        );
    }

    private function toEntity(VideoDAO $model): Video
    {
        return new Video(
            id:                 Id::fromString($model->id),
            title:              new Title($model->title),
            publishedAt:        PublishedAt::fromDateTimeImmutable($model->published_at->toDateTimeImmutable()),
            url:                new Url($model->url),
            localPath:          $model->local_path ? new LocalPath($model->local_path) : null,
            audioPath:          $model->audio_path ? new AudioPath($model->audio_path) : null,
            transcription:      $model->transcription ?? [],
            publishedShortIds:  $model->published_short_ids ?? [],
            channelId:          $model->channel_id,
        );
    }
}
