<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Repositories\Eloquent;

use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaId;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\Entities\VideoCollection;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\Resolution;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\TotalClips;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoModel;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Helmreel\VideoProduction\Video\Infrastructure\DAO\VideoDAO;

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
                'user_id' => $video->userId()->value(),
                'script_id' => $video->script()->id()->value(),
                'avatar_id' => $video->avatarId()?->value(),
                'title' => $video->title()->value(),
                'description' => $video->description()->value(),
                'category' => $video->category()->value,
                'resolution' => $video->resolution()->value,
                'model' => $video->model()->value,
                'total_clips' => $video->totalClips()->value(),
                'language' => $video->language()->value,
                'generation_id' => $video->generationId()?->value(),
                'video_local_path' => $video->videoLocalPath()?->value(),
                'media_id' => $video->mediaId()?->value(),
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

    /**
     * @return Video[]
     */
    public function findByUserId(IntegerId $userId): array
    {
        return VideoDAO::where('user_id', $userId->value())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (VideoDAO $model) => $this->toEntity($model))
            ->all();
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
            userId: new IntegerId((int) $model->user_id),
            script: $script,
            title: Title::fromString($model->title),
            description: Description::fromString($model->description),
            category: VideoCategory::from($model->category),
            createdAt: new DateTime($model->created_at->toDateTimeImmutable()),
            resolution: $model->resolution ? Resolution::fromString($model->resolution) : Resolution::HD,
            totalClips: new TotalClips((int) ($model->total_clips ?? 5)),
            language: $model->language ? Language::from($model->language) : Language::SPANISH,
            model: $model->model ? VideoModel::from($model->model) : VideoModel::VEO_31,
            avatarId: $model->avatar_id ? AvatarId::fromString($model->avatar_id) : null,
            generationId: $model->generation_id ? GenerationId::fromString($model->generation_id) : null,
            videoLocalPath: $model->video_local_path ? LocalPath::fromString($model->video_local_path) : null,
            completedAt: $model->completed_at ? new DateTime($model->completed_at->toDateTimeImmutable()) : null,
            mediaId: $model->media_id ? MediaId::fromString($model->media_id) : null,
        );
    }
}
