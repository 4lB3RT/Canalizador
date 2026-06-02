<?php

declare(strict_types=1);

namespace Helmreel\Shared\Header\Application\UseCases\GetHeader;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

final readonly class GetHeaderRequest
{
    public function __construct(
        public IntegerId $userId,
    ) {
    }
}
