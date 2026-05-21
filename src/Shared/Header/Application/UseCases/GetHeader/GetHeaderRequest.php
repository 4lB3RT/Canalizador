<?php

declare(strict_types=1);

namespace Canalizador\Shared\Header\Application\UseCases\GetHeader;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

final readonly class GetHeaderRequest
{
    public function __construct(
        public IntegerId $userId,
    ) {
    }
}
