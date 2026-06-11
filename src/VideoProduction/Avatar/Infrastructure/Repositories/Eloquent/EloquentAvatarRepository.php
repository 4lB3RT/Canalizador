<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\Eloquent;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Avatar\Domain\Entities\AvatarMedia;
use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarMediaType;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Helmreel\VideoProduction\Avatar\Infrastructure\DAO\AvatarDAO;
use Helmreel\VideoProduction\Media\Domain\Exceptions\MediaNotFound;
use Helmreel\VideoProduction\Media\Domain\Repositories\MediaRepository;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaId;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Facades\DB;

final class EloquentAvatarRepository implements AvatarRepository
{
    public function __construct(
        private readonly Clock $clock,
        private readonly MediaRepository $mediaRepository,
    ) {
    }

    public function save(Avatar $avatar): void
    {
        AvatarDAO::updateOrCreate(
            ['id' => $avatar->id()->value()],
            [
                'user_id' => $avatar->userId()->value(),
                'name' => $avatar->name()->value(),
                'profile_image_path' => $avatar->profileImagePath()->value(),
                'biography' => $avatar->biography()->value(),
                'presentation_style' => $avatar->presentationStyle()->value,
                'category' => $avatar->category()->value,
                'description' => $avatar->description()->value(),
                'voice_id' => $avatar->voiceId()?->value(),
                'created_at' => $avatar->createdAt()->value(),
                'updated_at' => $avatar->updatedAt()?->value() ?? now(),
            ]
        );

        $this->syncMedia($avatar);
    }

    private function syncMedia(Avatar $avatar): void
    {
        $avatarId = $avatar->id()->value();

        DB::table('avatar_media')->where('avatar_id', $avatarId)->delete();

        $rows = array_map(
            fn (AvatarMedia $m) => [
                'avatar_id' => $avatarId,
                'media_id' => $m->media()->id()->value(),
                'type' => $m->type()->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $avatar->media(),
        );

        if (!empty($rows)) {
            DB::table('avatar_media')->insert($rows);
        }
    }

    /**
     * @throws AvatarNotFound
     */
    public function findById(AvatarId $id): Avatar
    {
        $model = AvatarDAO::find($id->value());

        if (!$model) {
            throw AvatarNotFound::withId($id->value());
        }

        return $this->toEntity($model);
    }

    /**
     * @return Avatar[]
     */
    public function findByUserId(IntegerId $userId): array
    {
        $models = AvatarDAO::where('user_id', $userId->value())->get();

        $avatars = [];
        foreach ($models as $model) {
            $avatars[] = $this->toEntity($model);
        }

        return $avatars;
    }

    public function delete(AvatarId $id): void
    {
        DB::table('avatar_media')->where('avatar_id', $id->value())->delete();
        AvatarDAO::destroy($id->value());
    }

    private function toEntity(AvatarDAO $model): Avatar
    {
        $createdAt = $model->created_at
            ? new DateTime($model->created_at->toDateTimeImmutable())
            : new DateTime(new \DateTimeImmutable());

        $updatedAt = $model->updated_at
            ? new DateTime($model->updated_at->toDateTimeImmutable())
            : null;

        return new Avatar(
            id: AvatarId::fromString($model->id),
            userId: new IntegerId($model->user_id),
            voiceId: $model->voice_id ? VoiceId::fromString($model->voice_id) : null,
            name: AvatarName::fromString($model->name),
            profileImagePath: LocalPath::fromString($model->profile_image_path),
            createdAt: $createdAt,
            biography: Biography::fromString($model->biography ?? ''),
            presentationStyle: PresentationStyle::fromString($model->presentation_style ?? 'casual'),
            category: Category::fromString($model->category ?? 'gaming'),
            description: AvatarDescription::fromString($model->description ?? ''),
            media: $this->loadMedia($model->id),
            updatedAt: $updatedAt,
            clock: $this->clock,
        );
    }

    /**
     * @return AvatarMedia[]
     */
    private function loadMedia(string $avatarId): array
    {
        $rows = DB::table('avatar_media')
            ->where('avatar_id', $avatarId)
            ->get();

        $media = [];
        foreach ($rows as $row) {
            try {
                $entity = $this->mediaRepository->findById(MediaId::fromString($row->media_id));
            } catch (MediaNotFound) {
                continue;
            }

            $media[] = new AvatarMedia($entity, AvatarMediaType::fromString($row->type));
        }

        return $media;
    }
}
