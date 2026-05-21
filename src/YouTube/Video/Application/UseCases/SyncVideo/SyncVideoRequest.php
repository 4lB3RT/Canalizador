<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\SyncVideo;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;

final readonly class SyncVideoRequest
{
    public function __construct(
        public PlatformId $platformId,
        public IntegerId $userId,
    ) {
    }
}
