<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Infrastructure\Repositories\Eloquent;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Shared\Domain\ValueObjects\Url;
use Helmreel\VideoProduction\Clip\Domain\Entities\Clip;
use Helmreel\VideoProduction\Clip\Domain\Entities\ClipCollection;
use Helmreel\VideoProduction\Clip\Domain\Exceptions\ClipNotFound;
use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Helmreel\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Helmreel\VideoProduction\Clip\Domain\ValueObjects\ClipStatus;
use Helmreel\VideoProduction\Clip\Domain\ValueObjects\Sequence;
use Helmreel\VideoProduction\Clip\Infrastructure\DAO\ClipDAO;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;

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
                'script' => $clip->script(),
                'status' => $clip->status()->value,
                'local_path' => $clip->localPath()?->value(),
                'video_uri' => $clip->videoUri()?->value(),
                'last_frame_path' => $clip->lastFramePath()?->value(),
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

    public function deleteByVideoId(VideoId $videoId): void
    {
        ClipDAO::where('video_id', $videoId->value())->delete();
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
            script: $model->script,
            localPath: $model->local_path ? LocalPath::fromString($model->local_path) : null,
            videoUri: $model->video_uri ? Url::fromString($model->video_uri) : null,
            completedAt: $model->completed_at ? new DateTime($model->completed_at->toDateTimeImmutable()) : null,
            lastFramePath: $model->last_frame_path ? LocalPath::fromString($model->last_frame_path) : null,
        );
    }
}
