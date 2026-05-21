<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\Repositories;

use Canalizador\YouTube\Channel\Domain\Entities\ChannelGoogleToken;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelGoogleTokenNotFound;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;

interface ChannelGoogleTokenRepository
{
    /**
     * @throws ChannelGoogleTokenNotFound
     */
    public function findByChannelId(ChannelId $channelId): ChannelGoogleToken;

    public function save(ChannelGoogleToken $token): void;

    public function deleteByChannelId(ChannelId $channelId): void;
}
