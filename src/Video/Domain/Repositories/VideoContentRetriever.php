<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Illuminate\Http\Client\ConnectionException;

interface VideoContentRetriever
{
    /**
     * @throws VideoGenerationFailed
     * @throws ConnectionException
     */
    public function retrieve(Video $video): void;
}
