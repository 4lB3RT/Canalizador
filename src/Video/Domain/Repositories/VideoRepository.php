<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Entities\VideoCollection;
use Canalizador\Video\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function save(Video $video): void;

    public function findById(VideoId $id): ?Video;

    public function findByScriptId(ScriptId $scriptId): VideoCollection;

    public function delete(VideoId $id): void;
}
