<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Eloquent;

use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Video\Domain\Entities\Clip;
use Canalizador\Video\Domain\Entities\ClipCollection;
use Canalizador\Video\Domain\Exceptions\ClipNotFound;
use Canalizador\Video\Domain\Repositories\ClipRepository;
use Canalizador\Video\Domain\ValueObjects\ClipId;
use Canalizador\Video\Domain\ValueObjects\ClipStatus;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Sequence;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\Video\Infrastructure\DAO\ClipDAO;

final class EloquentClipRepository implements ClipRepository
{
    public function save(Clip $clip): void
    {
        ClipDAO::updateOrCreate(
            ['id' => $clip->id()->value()],
            [
                'video_id' => $clip->videoId()->value(),
                'sequence' => $clip->sequence()->value(),
                'generation_id' => $clip->generationId()->value(),
                'status' => $clip->status()->value,
                'local_path' => $clip->localPath()?->value(),
                'video_uri' => $clip->videoUri()?->value(),
                'created_at' => $clip->createdAt()->value(),
                'completed_at' => $clip->completedAt()?->value(),
            ]
        );
    }

    /**
     * @throws ClipNotFound
     */
    public function findById(ClipId $id): Clip
    {
        $model = ClipDAO::find($id->value());

        if (!$model) {
            throw ClipNotFound::withId($id->value());
        }

        return $this->toEntity($model);
    }

    public function findByVideoId(VideoId $videoId): ClipCollection
    {
        $models = ClipDAO::where('video_id', $videoId->value())->get();

        $clips = [];
        foreach ($models as $model) {
            $clips[] = $this->toEntity($model);
        }

        return new ClipCollection($clips);
    }

    private function toEntity(ClipDAO $model): Clip
    {
        return new Clip(
            id: ClipId::fromString($model->id),
            videoId: VideoId::fromString($model->video_id),
            sequence: Sequence::fromInt($model->sequence),
            generationId: GenerationId::fromString($model->generation_id),
            status: ClipStatus::from($model->status),
            createdAt: new DateTime($model->created_at->toDateTimeImmutable()),
            localPath: $model->local_path ? LocalPath::fromString($model->local_path) : null,
            videoUri: $model->video_uri ? Url::fromString($model->video_uri) : null,
            completedAt: $model->completed_at ? new DateTime($model->completed_at->toDateTimeImmutable()) : null,
        );
    }
}
