<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Media\Domain\Repositories;

use Helmreel\VideoProduction\Media\Domain\Entities\Media;
use Helmreel\VideoProduction\Media\Domain\Exceptions\MediaNotFound;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaId;

interface MediaRepository
{
    public function save(Media $media): void;

    /**
     * @throws MediaNotFound
     */
    public function findById(MediaId $id): Media;

    public function delete(MediaId $id): void;
}
