<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use App\Services\GoogleClientService;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\ChannelVideoFinder;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeServiceFactory;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;
use Google_Service_YouTube;

final class GoogleYouTubeChannelVideoFinder implements ChannelVideoFinder
{
    public function __construct(
        private readonly GoogleClientService $googleClientService,
        private readonly YouTubeServiceFactory $youtubeServiceFactory,
    ) {
    }

    /**
     * @throws YouTubeOperationFailed
     */
    public function findLatestByChannelId(string $channelId): YouTubeVideoId
    {
        $client         = $this->googleClientService->buildYouTubeClient();
        $youtubeService = $this->youtubeServiceFactory->create($client);

        $uploadsPlaylistId = $this->getUploadsPlaylistId($youtubeService, $channelId);
        $latestVideoId     = $this->getLatestVideoFromPlaylist($youtubeService, $uploadsPlaylistId);

        return new YouTubeVideoId($latestVideoId);
    }

    /**
     * @throws YouTubeOperationFailed
     */
    private function getUploadsPlaylistId(Google_Service_YouTube $service, string $channelId): string
    {
        $response = $service->channels->listChannels('contentDetails', ['id' => $channelId]);
        $items    = $response->getItems();

        if (empty($items)) {
            throw YouTubeOperationFailed::channelNotFound($channelId);
        }

        $uploadsPlaylistId = $items[0]->getContentDetails()->getRelatedPlaylists()->getUploads();

        if (empty($uploadsPlaylistId)) {
            throw YouTubeOperationFailed::channelNotFound($channelId);
        }

        return $uploadsPlaylistId;
    }

    /**
     * @throws YouTubeOperationFailed
     */
    private function getLatestVideoFromPlaylist(Google_Service_YouTube $service, string $playlistId): string
    {
        $response = $service->playlistItems->listPlaylistItems(
            'snippet',
            [
                'playlistId' => $playlistId,
                'maxResults' => 1,
            ]
        );

        $items = $response->getItems();

        if (empty($items)) {
            throw YouTubeOperationFailed::apiError("No videos found in playlist: {$playlistId}");
        }

        $videoId = $items[0]->getSnippet()->getResourceId()->getVideoId();

        if (empty($videoId)) {
            throw YouTubeOperationFailed::apiError("Could not extract video ID from playlist item");
        }

        return $videoId;
    }
}
