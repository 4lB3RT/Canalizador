<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\Repositories;

use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelMetadata;

interface ChannelMetadataRepository
{
    public function generateData(Channel $channel): ChannelMetadata;
}

