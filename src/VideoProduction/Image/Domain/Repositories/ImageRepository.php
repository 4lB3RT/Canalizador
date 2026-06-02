<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Image\Domain\Entities\Image;
use Helmreel\VideoProduction\Image\Domain\Entities\ImageCollection;
use Helmreel\VideoProduction\Image\Domain\Exceptions\ImageNotFound;
use Helmreel\VideoProduction\Image\Domain\ValueObjects\ImageId;

interface ImageRepository
{
    public function save(Image $image): void;

    /**
     * @throws ImageNotFound
     */
    public function findById(ImageId $id): Image;

    public function findByUserId(IntegerId $userId): ImageCollection;

    public function delete(ImageId $id): void;
}
