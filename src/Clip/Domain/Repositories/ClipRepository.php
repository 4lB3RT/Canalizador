<?php

declare(strict_types=1);

namespace Canalizador\Clip\Domain\Repositories;

use Canalizador\Clip\Domain\Entities\Clip;
use Canalizador\Clip\Domain\Entities\ClipCollection;
use Canalizador\Clip\Domain\Exceptions\ClipNotFound;
use Canalizador\Clip\Domain\ValueObjects\ClipId;
use Canalizador\Video\Domain\ValueObjects\VideoId;

interface ClipRepository
{
    public function save(Clip $clip): void;

    /**
     * @throws ClipNotFound
     */
    public function findById(ClipId $id): Clip;

    public function findByVideoId(VideoId $videoId): ClipCollection;
}
