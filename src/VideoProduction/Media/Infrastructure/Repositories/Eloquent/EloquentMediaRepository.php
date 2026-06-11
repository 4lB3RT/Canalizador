<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Media\Infrastructure\Repositories\Eloquent;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Media\Domain\Entities\Media;
use Helmreel\VideoProduction\Media\Domain\Exceptions\MediaNotFound;
use Helmreel\VideoProduction\Media\Domain\Repositories\MediaRepository;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaId;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaType;
use Helmreel\VideoProduction\Media\Infrastructure\DAO\MediaDAO;

final class EloquentMediaRepository implements MediaRepository
{
    public function save(Media $media): void
    {
        MediaDAO::updateOrCreate(
            ['id' => $media->id()->value()],
            [
                'user_id' => $media->userId()->value(),
                'type' => $media->type()->value,
                'path' => $media->path()->value(),
                'created_at' => $media->createdAt()->value(),
                'updated_at' => $media->updatedAt()?->value() ?? now(),
            ]
        );
    }

    /**
     * @throws MediaNotFound
     */
    public function findById(MediaId $id): Media
    {
        $dao = MediaDAO::find($id->value());

        if (!$dao) {
            throw MediaNotFound::withId($id->value());
        }

        return new Media(
            id: new MediaId($dao->id),
            userId: new IntegerId((int) $dao->user_id),
            type: MediaType::fromString($dao->type),
            path: LocalPath::fromString($dao->path),
            createdAt: new DateTime($dao->created_at->toDateTimeImmutable()),
            updatedAt: $dao->updated_at ? new DateTime($dao->updated_at->toDateTimeImmutable()) : null,
        );
    }

    public function delete(MediaId $id): void
    {
        MediaDAO::query()->where('id', $id->value())->delete();
    }
}
