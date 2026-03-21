<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Factories;

use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoPublisher;

interface VideoPublisherFactory
{
    /**
     * @throws YouTubeOperationFailed If the platform is not supported
     */
    public function create(string $platform): VideoPublisher;
}
