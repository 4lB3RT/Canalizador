<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Infrastructure\Repositories\Eloquent;

use Canalizador\Avatar\Domain\Entities\Avatar;
use Canalizador\Avatar\Domain\Exceptions\AvatarNotFound;
use Canalizador\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Avatar\Domain\ValueObjects\AvatarName;
use Canalizador\Avatar\Domain\ValueObjects\Biography;
use Canalizador\Avatar\Domain\ValueObjects\PresentationStyle;
use Canalizador\Avatar\Infrastructure\DAO\AvatarDAO;
use Canalizador\Image\Domain\Entities\ImageCollection;
use Canalizador\Image\Domain\Repositories\ImageRepository;
use Canalizador\Image\Domain\ValueObjects\ImageId;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Illuminate\Support\Facades\DB;

final class EloquentAvatarRepository implements AvatarRepository
{
    public function __construct(
        private readonly Clock $clock,
        private readonly ImageRepository $imageRepository
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
                'description' => $avatar->description()->value(),
                'created_at' => $avatar->createdAt()->value(),
                'updated_at' => $avatar->updatedAt()?->value() ?? now(),
            ]
        );

        $this->syncImages($avatar);
    }

    private function syncImages(Avatar $avatar): void
    {
        $avatarId = $avatar->id()->value();
        $imageIds = array_map(
            fn ($image) => $image->id()->value(),
            $avatar->images()->items()
        );

        DB::table('avatar_image')
            ->where('avatar_id', $avatarId)
            ->delete();

        if (!empty($imageIds)) {
            $pivotData = array_map(
                fn ($imageId) => [
                    'avatar_id' => $avatarId,
                    'image_id' => $imageId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $imageIds
            );

            DB::table('avatar_image')->insert($pivotData);
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

        $images = $this->loadImages($model->id);

        return new Avatar(
            id: AvatarId::fromString($model->id),
            userId: new IntegerId($model->user_id),
            name: AvatarName::fromString($model->name),
            profileImagePath: LocalPath::fromString($model->profile_image_path),
            createdAt: $createdAt,
            biography: Biography::fromString($model->biography ?? ''),
            presentationStyle: PresentationStyle::fromString($model->presentation_style ?? 'casual'),
            description: AvatarDescription::fromString($model->description ?? ''),
            images: $images,
            updatedAt: $updatedAt,
            clock: $this->clock,
        );
    }

    private function loadImages(string $avatarId): ImageCollection
    {
        $pivotRecords = DB::table('avatar_image')
            ->where('avatar_id', $avatarId)
            ->pluck('image_id')
            ->toArray();

        if (empty($pivotRecords)) {
            return ImageCollection::empty();
        }

        $images = [];
        foreach ($pivotRecords as $imageId) {
            try {
                $images[] = $this->imageRepository->findById(ImageId::fromString($imageId));
            } catch (\Exception $e) {
                continue;
            }
        }

        return new ImageCollection($images);
    }
}

