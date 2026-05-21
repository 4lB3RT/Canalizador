<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\LinkChannel;

use Canalizador\YouTube\Channel\Domain\Entities\Channel;

final readonly class LinkChannelResponse
{
    public function __construct(
        public Channel $channel,
    ) {
    }
}
