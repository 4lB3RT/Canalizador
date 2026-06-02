<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Domain\Repositories;

use Helmreel\YouTube\Channel\Domain\Entities\ChannelGoogleToken;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelGoogleTokenNotFound;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;

interface ChannelGoogleTokenRepository
{
    /**
     * @throws ChannelGoogleTokenNotFound
     */
    public function findByChannelId(ChannelId $channelId): ChannelGoogleToken;

    public function save(ChannelGoogleToken $token): void;

    public function deleteByChannelId(ChannelId $channelId): void;
}
