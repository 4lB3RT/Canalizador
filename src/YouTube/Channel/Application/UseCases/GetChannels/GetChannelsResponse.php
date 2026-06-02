<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\GetChannels;

use Helmreel\Shared\Shared\Domain\ValueObjects\Page;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;
use Helmreel\Shared\Shared\Domain\ValueObjects\Total;
use Helmreel\YouTube\Channel\Domain\Entities\ChannelCollection;

final readonly class GetChannelsResponse
{
    public function __construct(
        public ChannelCollection $channels,
        public Total $total,
        public Pagination $pagination,
    ) {
    }

    public function lastPage(): Page
    {
        $perPage = $this->pagination->perPage()->value();
        $total = $this->total->value();

        $value = (int) max(1, (int) ceil($total / $perPage));

        return Page::fromInt($value);
    }
}
