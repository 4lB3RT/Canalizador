<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Infrastructure\Commands;

use Helmreel\YouTube\Channel\Application\UseCases\RegisterChannel\RegisterChannel;
use Helmreel\YouTube\Channel\Application\UseCases\RegisterChannel\RegisterChannelRequest;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Illuminate\Console\Command;

class RegisterChannelCommand extends Command
{
    protected $signature   = 'youtube:register-channel {channelId : The YouTube channel ID} {userId : The application user ID}';
    protected $description = 'Register a YouTube channel in the local database';

    /* @throws ChannelNotFound */
    public function handle(RegisterChannel $registerChannel): int
    {
        $registerChannel->execute(new RegisterChannelRequest(
            channelId: $this->argument('channelId'),
            userId:    (int) $this->argument('userId'),
        ));

        return self::SUCCESS;
    }
}
