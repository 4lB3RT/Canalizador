<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Factories;

use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\Repositories\VideoPublisher;

interface VideoPublisherFactory
{
    /**
     * @throws YouTubeOperationFailed If the platform is not supported
     */
    public function create(string $platform): VideoPublisher;
}
