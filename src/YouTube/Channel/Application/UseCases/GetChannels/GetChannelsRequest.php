<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\GetChannels;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;

final readonly class GetChannelsRequest
{
    public function __construct(
        public IntegerId $userId,
        public Pagination $pagination,
    ) {
    }
}
