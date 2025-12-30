<?php

declare(strict_types=1);

namespace Canalizador\Channel\Domain\Repositories;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Channel\Domain\ValueObjects\ChannelMetadata;

interface ChannelMetadataRepository
{
    public function generateData(Channel $channel): ChannelMetadata;
}

