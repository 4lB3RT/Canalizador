<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Image\Domain\Repositories;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\VideoProduction\Image\Domain\Entities\Image;
use Canalizador\VideoProduction\Image\Domain\Entities\ImageCollection;
use Canalizador\VideoProduction\Image\Domain\Exceptions\ImageNotFound;
use Canalizador\VideoProduction\Image\Domain\ValueObjects\ImageId;

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
