<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Commands;

use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Video\Application\UseCases\SyncLastVideo\SyncLastVideo;
use Canalizador\YouTube\Video\Application\UseCases\SyncLastVideo\SyncLastVideoRequest;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use Illuminate\Console\Command;
use Throwable;

class SyncLastVideoCommand extends Command
{
    protected $signature   = 'youtube:sync-last-video {channelId : The YouTube channel ID}';
    protected $description = 'Sync the latest non-Short video from a YouTube channel';

    /**
     * @throws DateMalformedStringException
     * @throws Throwable
     * @throws VideoNotFound
     * @throws ChannelNotFound
     * @throws DateMalformedIntervalStringException
     */
    public function handle(SyncLastVideo $syncLastVideo): int
    {
        $channelId = $this->argument('channelId');

        $syncLastVideo->execute(new SyncLastVideoRequest(channelId: $channelId));

        return self::SUCCESS;
    }
}
