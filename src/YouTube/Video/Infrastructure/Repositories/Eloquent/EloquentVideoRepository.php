<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\Eloquent;

use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Infrastructure\DAO\VideoDAO;
use Canalizador\YouTube\Video\Infrastructure\DataTransformers\VideoDataTransformer;

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

        return VideoDataTransformer::fromArray([
            'id'               => $model->id,
            'title'            => $model->title,
            'published_at'     => $model->published_at->format('Y-m-d H:i:s'),
            'metrics'          => [],
            'category'         => $model->category ?? 'video',
            'url'              => $model->url,
            'video_local_path' => $model->local_path,
            'audio_local_path' => $model->audio_path,
            'transcription'    => $model->transcription,
            'duration'         => $model->duration,
        ]);
    }

    public function save(Video $video): void
    {
        $data = $video->toArray();

        VideoDAO::updateOrCreate(
            ['id' => $data['id']],
            [
                'title'        => $data['title'],
                'url'          => $data['url'],
                'published_at' => $data['published_at'],
                'local_path'   => $data['video_local_path'],
                'audio_path'   => $data['audio_local_path'],
                'transcription' => $data['transcription'],
                'category'     => $data['category'],
                'duration'     => $data['duration'],
            ]
        );
    }
}
