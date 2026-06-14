<?php

declare(strict_types=1);

namespace Helmreel\Shared\Media\Domain\Repositories;

use Helmreel\Shared\Media\Domain\Entities\Media;
use Helmreel\Shared\Media\Domain\Exceptions\MediaNotFound;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;

interface MediaRepository
{
    public function save(Media $media): void;

    /**
     * @throws MediaNotFound
     */
    public function findById(MediaId $id): Media;

    public function delete(MediaId $id): void;
}
