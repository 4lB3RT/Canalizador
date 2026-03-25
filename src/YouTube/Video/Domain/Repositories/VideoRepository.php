<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;

interface VideoRepository
{
    /**
     * @throws VideoNotFound
     */
    public function findById(Id $id): Video;
}
