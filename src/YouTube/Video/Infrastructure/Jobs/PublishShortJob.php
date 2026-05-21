<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Jobs;

use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideoRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class PublishShortJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $videoId,
        public readonly string $platform = 'youtube',
    ) {
    }

    public function handle(PublishVideo $publishVideo): void
    {
        $publishVideo->execute(new PublishVideoRequest(
            videoId:  $this->videoId,
            platform: $this->platform,
        ));
    }

    public function failed(Throwable $e): void
    {
        report($e);
    }
}
