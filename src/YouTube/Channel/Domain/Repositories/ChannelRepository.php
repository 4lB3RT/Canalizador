<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\Repositories;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\Entities\ChannelCollection;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;

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

