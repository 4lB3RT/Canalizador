<?php

declare(strict_types=1);

namespace Helmreel\Shared\Header\Application\UseCases\GetHeader;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Total;

final readonly class GetHeaderResponse
{
    public function __construct(
        public IntegerId $userId,
        public string $name,
        public string $email,
        public bool $googleLinked,
        public Total $channelsCount,
        public ?string $avatarPath = null,
    ) {
    }
}
