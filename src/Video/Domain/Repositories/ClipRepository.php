<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Video\Domain\Entities\Clip;
use Canalizador\Video\Domain\Entities\ClipCollection;
use Canalizador\Video\Domain\Exceptions\ClipNotFound;
use Canalizador\Video\Domain\ValueObjects\ClipId;
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
