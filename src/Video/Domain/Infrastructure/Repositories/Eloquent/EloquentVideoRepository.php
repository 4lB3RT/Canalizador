<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Infrastructure\Repositories\Eloquent;

use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Entities\VideoCollection;
use Canalizador\Video\Domain\Infrastructure\DAO\VideoDAO;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final class EloquentVideoRepository implements VideoRepository
{
    public function save(Video $video): void
    {
        VideoDAO::updateOrCreate(
            ['generated_video_id' => $video->id()->value()],
            [
                'script_id' => $video->scriptId()->value(),
                'title' => $video->title()->value(),
                'video_local_path' => $video->videoLocalPath()?->value(),
                'audio_local_path' => $video->audioLocalPath()?->value(),
                'created_at' => $video->createdAt()->value(),
                'completed_at' => $video->completedAt()?->value(),
            ]
        );
    }

    public function findById(VideoId $id): ?Video
    {
        $model = VideoDAO::find($id->value());

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByScriptId(ScriptId $scriptId): VideoCollection
    {
        $models = VideoDAO::where('script_id', $scriptId->value())->get();

        $videos = [];
        foreach ($models as $model) {
            $videos[] = $this->toEntity($model);
        }

        return new VideoCollection($videos);
    }

    public function delete(VideoId $id): void
    {
        VideoDAO::destroy($id->value());
    }

    private function toEntity(VideoDAO $model): Video
    {
        return new Video(
            id: VideoId::fromString($model->generated_video_id),
            scriptId: ScriptId::fromString($model->script_id),
            title: Title::fromString($model->title),
            createdAt: new DateTime($model->created_at),
            videoLocalPath: $model->video_local_path ? LocalPath::fromString($model->video_local_path) : null,
            audioLocalPath: $model->audio_local_path ? LocalPath::fromString($model->audio_local_path) : null,
            completedAt: $model->completed_at ? new DateTime($model->completed_at) : null,
        );
    }
}
