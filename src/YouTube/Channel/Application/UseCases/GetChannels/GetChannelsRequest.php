<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\GetChannels;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;

final readonly class GetChannelsRequest
{
    public function __construct(
        public IntegerId $userId,
        public Pagination $pagination,
    ) {
    }
}
