<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo\DownloadLatestChannelVideo;
use Helmreel\YouTube\Video\Application\UseCases\DownloadLatestChannelVideo\DownloadLatestChannelVideoRequest;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class DownloadLatestChannelVideoController extends Controller
{
    public function __construct(
        private readonly DownloadLatestChannelVideo $downloadLatestChannelVideo,
    ) {
    }

    public function __invoke(string $channelId): JsonResponse
    {
        try {
            $response = $this->downloadLatestChannelVideo->execute(
                new DownloadLatestChannelVideoRequest(channelId: $channelId)
            );

            return response()->json($response->toArray(), 200);
        } catch (YouTubeOperationFailed $e) {
            return response()->json([
                'error'   => 'Failed to download latest channel video',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
