<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Repositories;

use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Illuminate\Http\Client\ConnectionException;

interface VideoContentRetriever
{
    /**
     * @throws VideoGenerationFailed
     * @throws ConnectionException
     */
    public function retrieve(Video $video): void;
}
