<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Domain\Repositories;

use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelMetadata;

interface ChannelMetadataRepository
{
    public function generateData(Channel $channel): ChannelMetadata;
}

