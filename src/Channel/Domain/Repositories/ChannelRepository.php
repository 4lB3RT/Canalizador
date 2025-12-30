<?php

declare(strict_types=1);

namespace Canalizador\Channel\Domain\Repositories;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Channel\Domain\Entities\ChannelCollection;
use Canalizador\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;

interface ChannelRepository
{
    public function save(Channel $channel): void;

    /**
     * @throws ChannelNotFound
     */
    public function findById(ChannelId $id): Channel;

    public function findByUserId(IntegerId $userId): ChannelCollection;

    public function delete(ChannelId $id): void;
}

