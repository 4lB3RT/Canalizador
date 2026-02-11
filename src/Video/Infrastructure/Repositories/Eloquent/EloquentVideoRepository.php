<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Eloquent;

use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Entities\VideoCollection;
use Canalizador\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\Description;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\Video\Infrastructure\DAO\VideoDAO;

final class EloquentVideoRepository implements VideoRepository
{
    public function __construct(
        private ScriptRepository $scriptRepository
    ) {
    }

    public function save(Video $video): void
    {
        VideoDAO::updateOrCreate(
            ['id' => $video->id()->value()],
            [
                'script_id' => $video->script()->id()->value(),
                'channel_id' => $video->channelId()->value(),
                'avatar_id' => $video->avatarId()?->value(),
                'title' => $video->title()->value(),
                'description' => $video->description()->value(),
                'category' => $video->category()->value,
                'generation_id' => $video->generationId()?->value(),
                'video_local_path' => $video->videoLocalPath()?->value(),
                'created_at' => $video->createdAt()->value(),
                'completed_at' => $video->completedAt()?->value(),
            ]
        );
    }

    /**
     * @throws VideoNotFound
     */
    public function findById(VideoId $id): Video
    {
        $model = VideoDAO::find($id->value());

        if (!$model) {
            throw VideoNotFound::withId($id->value());
        }

        return $this->toEntity($model);
    }

    public function getByScriptId(ScriptId $scriptId): VideoCollection
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
        $scriptId = ScriptId::fromString($model->script_id);
        $script = $this->scriptRepository->findById($scriptId);

        if (!$script) {
            throw new \RuntimeException("Script not found for script_id: {$model->script_id}");
        }

        return new Video(
            id: VideoId::fromString($model->id),
            script: $script,
            channelId: ChannelId::fromString($model->channel_id),
            title: Title::fromString($model->title),
            description: Description::fromString($model->description),
            category: VideoCategory::from($model->category),
            createdAt: new DateTime($model->created_at->toDateTimeImmutable()),
            avatarId: $model->avatar_id ? AvatarId::fromString($model->avatar_id) : null,
            generationId: $model->generation_id ? GenerationId::fromString($model->generation_id) : null,
            videoLocalPath: $model->video_local_path ? LocalPath::fromString($model->video_local_path) : null,
            completedAt: $model->completed_at ? new DateTime($model->completed_at) : null,
        );
    }
}
