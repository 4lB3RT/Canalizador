<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\LinkChannel;

use Helmreel\YouTube\Channel\Domain\Entities\Channel;

final readonly class LinkChannelResponse
{
    public function __construct(
        public Channel $channel,
    ) {
    }
}
