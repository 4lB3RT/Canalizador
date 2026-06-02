<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\SyncVideo;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\YouTube\Video\Domain\ValueObjects\PlatformId;

final readonly class SyncVideoRequest
{
    public function __construct(
        public PlatformId $platformId,
        public IntegerId $userId,
    ) {
    }
}
