<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;
use Helmreel\Shared\Shared\Domain\ValueObjects\Total;
use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\Entities\ChannelCollection;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;

interface ChannelRepository
{
    public function save(Channel $channel): void;

    /**
     * @throws ChannelNotFound
     */
    public function findById(ChannelId $id): Channel;

    public function findByUserId(IntegerId $userId, ?Pagination $pagination = null): ChannelCollection;

    public function countByUserId(IntegerId $userId): Total;

    public function findAllWithAutoSync(): ChannelCollection;

    public function delete(ChannelId $id): void;
}

