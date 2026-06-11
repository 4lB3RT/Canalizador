<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\Entities\VideoCollection;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function save(Video $video): void;

    /**
     * @throws VideoNotFound
     */
    public function findById(VideoId $id): Video;

    /**
     * @return Video[]
     */
    public function findByUserId(IntegerId $userId): array;

    public function getByScriptId(ScriptId $scriptId): VideoCollection;

    public function delete(VideoId $id): void;
}
