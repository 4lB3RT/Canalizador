<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Infrastructure\Repositories\Eloquent;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Image\Domain\Entities\Image;
use Helmreel\VideoProduction\Image\Domain\Entities\ImageCollection;
use Helmreel\VideoProduction\Image\Domain\Exceptions\ImageNotFound;
use Helmreel\VideoProduction\Image\Domain\Repositories\ImageRepository;
use Helmreel\VideoProduction\Image\Domain\ValueObjects\ImageId;
use Helmreel\VideoProduction\Image\Domain\ValueObjects\ImageType;
use Helmreel\VideoProduction\Image\Infrastructure\DAO\ImageDAO;

final class EloquentImageRepository implements ImageRepository
{
    public function save(Image $image): void
    {
        ImageDAO::updateOrCreate(
            ['id' => $image->id()->value()],
            [
                'user_id' => $image->userId()->value(),
                'path' => $image->path()->value(),
                'type' => $image->type()->value,
                'created_at' => $image->createdAt()->value(),
                'updated_at' => $image->updatedAt()?->value() ?? now(),
            ]
        );
    }

    /**
     * @throws ImageNotFound
     */
    public function findById(ImageId $id): Image
    {
        $model = ImageDAO::find($id->value());

        if (!$model) {
            throw ImageNotFound::withId($id->value());
        }

        return $this->toEntity($model);
    }

    public function findByUserId(IntegerId $userId): ImageCollection
    {
        $models = ImageDAO::where('user_id', $userId->value())->get();

        $images = [];
        foreach ($models as $model) {
            $images[] = $this->toEntity($model);
        }

        return new ImageCollection($images);
    }

    public function delete(ImageId $id): void
    {
        ImageDAO::destroy($id->value());
    }

    private function toEntity(ImageDAO $model): Image
    {
        $createdAt = $model->created_at
            ? new DateTime($model->created_at->toDateTimeImmutable())
            : new DateTime(new \DateTimeImmutable());

        $updatedAt = $model->updated_at
            ? new DateTime($model->updated_at->toDateTimeImmutable())
            : null;

        return new Image(
            id: ImageId::fromString($model->id),
            userId: new IntegerId($model->user_id),
            path: LocalPath::fromString($model->path),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            type: ImageType::fromString($model->type ?? 'generated'),
        );
    }
}
