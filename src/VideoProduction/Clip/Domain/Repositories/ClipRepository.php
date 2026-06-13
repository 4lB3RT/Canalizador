<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Domain\Repositories;

use Helmreel\VideoProduction\Clip\Domain\Entities\Clip;
use Helmreel\VideoProduction\Clip\Domain\Entities\ClipCollection;
use Helmreel\VideoProduction\Clip\Domain\Exceptions\ClipNotFound;
use Helmreel\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;

interface ClipRepository
{
    public function save(Clip $clip): void;

    /**
     * @throws ClipNotFound
     */
    public function findById(ClipId $id): Clip;

    public function findByVideoId(VideoId $videoId): ClipCollection;

    public function deleteByVideoId(VideoId $videoId): void;
}
