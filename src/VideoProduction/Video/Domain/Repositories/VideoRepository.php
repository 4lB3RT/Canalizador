<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Repositories;

use Canalizador\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\Entities\VideoCollection;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function save(Video $video): void;

    /**
     * @throws VideoNotFound
     */
    public function findById(VideoId $id): Video;

    public function getByScriptId(ScriptId $scriptId): VideoCollection;

    public function delete(VideoId $id): void;
}
