<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Infrastructure\Commands;

use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Video\Application\UseCases\SyncLastVideo\SyncLastVideo;
use Helmreel\YouTube\Video\Application\UseCases\SyncLastVideo\SyncLastVideoRequest;
use Illuminate\Console\Command;
use Throwable;

class SyncAutoChannelsCommand extends Command
{
    protected $signature   = 'youtube:sync-channels';
    protected $description = 'Sync the latest video for every channel with auto_sync enabled';

    public function handle(
        ChannelRepository $channelRepository,
        SyncLastVideo $syncLastVideo,
    ): int {
        $channels = $channelRepository->findAllWithAutoSync();

        if ($channels->isEmpty()) {
            $this->info('No channels with auto_sync enabled.');
            return self::SUCCESS;
        }

        $failures = 0;
        foreach ($channels as $channel) {
            /** @var Channel $channel */
            $id = $channel->id()->value();

            try {
                $syncLastVideo->execute(new SyncLastVideoRequest(channelId: $id));
                $this->info("Synced channel {$id}");
            } catch (Throwable $e) {
                $failures++;
                report($e);
                $this->error("Failed to sync channel {$id}: {$e->getMessage()}");
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
