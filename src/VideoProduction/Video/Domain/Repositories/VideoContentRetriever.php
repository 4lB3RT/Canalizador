<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Repositories;

use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Illuminate\Http\Client\ConnectionException;

interface VideoContentRetriever
{
    /**
     * @throws VideoGenerationFailed
     * @throws ConnectionException
     */
    public function retrieve(Video $video): void;
}
