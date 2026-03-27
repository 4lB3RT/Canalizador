<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Image\Infrastructure\Repositories\Eloquent;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\VideoProduction\Image\Domain\Entities\Image;
use Canalizador\VideoProduction\Image\Domain\Entities\ImageCollection;
use Canalizador\VideoProduction\Image\Domain\Exceptions\ImageNotFound;
use Canalizador\VideoProduction\Image\Domain\Repositories\ImageRepository;
use Canalizador\VideoProduction\Image\Domain\ValueObjects\ImageId;
use Canalizador\VideoProduction\Image\Infrastructure\DAO\ImageDAO;

final class EloquentImageRepository implements ImageRepository
{
    public function save(Image $image): void
    {
        ImageDAO::updateOrCreate(
            ['id' => $image->id()->value()],
            [
                'user_id' => $image->userId()->value(),
                'path' => $image->path()->value(),
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
        );
    }
}
