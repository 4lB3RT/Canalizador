<?php

declare(strict_types=1);

namespace Canalizador\Image\Domain\Repositories;

use Canalizador\Image\Domain\Entities\Image;
use Canalizador\Image\Domain\Entities\ImageCollection;
use Canalizador\Image\Domain\Exceptions\ImageNotFound;
use Canalizador\Image\Domain\ValueObjects\ImageId;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;

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
