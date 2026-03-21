<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\Repositories;

use Canalizador\VideoProduction\Clip\Domain\Entities\Clip;
use Canalizador\VideoProduction\Clip\Domain\Entities\ClipCollection;
use Canalizador\VideoProduction\Clip\Domain\Exceptions\ClipNotFound;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;

interface ClipRepository
{
    public function save(Clip $clip): void;

    /**
     * @throws ClipNotFound
     */
    public function findById(ClipId $id): Clip;

    public function findByVideoId(VideoId $videoId): ClipCollection;
}
