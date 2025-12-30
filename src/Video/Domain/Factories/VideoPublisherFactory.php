<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Factories;

use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Repositories\VideoPublisher;

interface VideoPublisherFactory
{
    /**
     * @throws VideoGenerationFailed If the platform is not supported
     */
    public function create(string $platform): VideoPublisher;
}
