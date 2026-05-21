<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Shared\Infrastructure\ClientAPI;

use App\Services\GoogleClientService;
use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Shared\Domain\Services\YouTubeServiceFactory;
use DateInterval;
use DateTimeImmutable;
use Google\Service\Exception;
use Google_Client;
use Google_Service_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_Channel;
use Google_Service_YouTube_ChannelBrandingSettings;
use Google_Service_YouTube_ChannelSettings;
use RuntimeException;

final readonly class YoutubeDataApiClient
{
    public function __construct(
        private string                 $apiKey,
        private ?GoogleClientService   $googleClientService = null,
        private ?YouTubeServiceFactory $youtubeServiceFactory = null,
    ) {
    }

    /**
     * @throws Google_Service_Exception
     * @throws Exception
     */
    public function getChannelById(string $channelId): ?array
    {
        $client         = $this->createClientWithoutAuth();
        $youtubeService = new Google_Service_YouTube($client);

        $response = $youtubeService->channels->listChannels(
            'snippet,contentDetails,statistics',
            ['id' => $channelId]
        );

        $items = $response->getItems();
        if (empty($items)) {
            return null;
        }

        $channel = $items[0];

        return json_decode(json_encode($channel), true);
    }

    public function getVideoById(string $videoId): ?array
    {
        try {
            $client         = $this->createClientWithoutAuth();
            $youtubeService = new Google_Service_YouTube($client);

            $response = $youtubeService->videos->listVideos(
                'snippet,contentDetails,statistics',
                ['id' => $videoId]
            );

            $items = $response->getItems();
            if (empty($items)) {
                return null;
            }

            $video = $items[0];

            return json_decode(json_encode($video), true);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @throws Google_Service_Exception
     * @throws RuntimeException
     * @throws Exception
     */
    public function updateChannel(Channel $channel): void
    {
        if ($this->googleClientService === null || $this->youtubeServiceFactory === null) {
            throw new RuntimeException('GoogleClientService and YouTubeServiceFactory are required for authenticated operations');
        }

        $channelId    = $channel->id()->value();
        $existingData = $this->getChannelById($channelId);
        if (!$existingData) {
            throw new RuntimeException('Channel not found on YouTube');
        }

        $client         = $this->googleClientService->buildYouTubeClient();
        $youtubeService = $this->youtubeServiceFactory->create($client);

        $response = $youtubeService->channels->listChannels('snippet,brandingSettings', ['id' => $channelId]);
        $items    = $response->getItems();

        if (empty($items)) {
            throw new RuntimeException('Channel not found on YouTube');
        }

        $existingChannel = $items[0];
        $channelSnippet  = $existingChannel->getSnippet();
        if ($channelSnippet) {
            $channelSnippet->setTitle($channel->title()->value());

            $youtubeChannelForSnippet = new Google_Service_YouTube_Channel();
            $youtubeChannelForSnippet->setId($channelId);
            $youtubeChannelForSnippet->setSnippet($channelSnippet);

            $youtubeService->channels->update('snippet', $youtubeChannelForSnippet);
        }

        $channelBrandingSettings = $existingChannel->getBrandingSettings();
        if (!$channelBrandingSettings) {
            $channelBrandingSettings = new Google_Service_YouTube_ChannelBrandingSettings();
        }

        $channelSettings = $channelBrandingSettings->getChannel();
        if (!$channelSettings) {
            $channelSettings = new Google_Service_YouTube_ChannelSettings();
        }

        $existingSnippet = $existingData['snippet'] ?? [];
        $defaultLanguage = !empty($existingSnippet['defaultLanguage'])
            ? strtolower($existingSnippet['defaultLanguage'])
            : $channel->country()->toLanguageCode();

        $channelSettings->setDescription($channel->description()->value());
        $channelSettings->setDefaultLanguage($defaultLanguage);
        $channelBrandingSettings->setChannel($channelSettings);

        $youtubeChannel = new Google_Service_YouTube_Channel();
        $youtubeChannel->setId($channelId);
        $youtubeChannel->setBrandingSettings($channelBrandingSettings);

        $youtubeService->channels->update('brandingSettings', $youtubeChannel);
    }

    /**
     * @throws Exception
     */
    public function getLastVideoIdByChannelId(string $channelId, ?string $categoryFilter = null): ?string
    {
        $client         = $this->createClientWithoutAuth();
        $youtubeService = new Google_Service_YouTube($client);

        $response = $youtubeService->search->listSearch('snippet', [
            'channelId'  => $channelId,
            'type'       => 'video',
            'order'      => 'date',
            'maxResults' => 5,
        ]);

        $items = $response->getItems();
        if (empty($items)) {
            return null;
        }

        $videoIds = array_map(static fn ($item) => $item->getId()->getVideoId(), $items);

        if ($categoryFilter === null) {
            return $videoIds[0];
        }

        $details = $youtubeService->videos->listVideos('contentDetails', ['id' => implode(',', $videoIds)]);
        $durations = [];
        foreach ($details->getItems() as $item) {
            $durations[$item->getId()] = $this->iso8601ToSeconds($item->getContentDetails()->getDuration());
        }

        foreach ($videoIds as $videoId) {
            $seconds = $durations[$videoId] ?? 0;

            if ($categoryFilter === 'short' && $seconds <= 180) {
                return $videoId;
            }

            if ($categoryFilter === 'video' && $seconds > 180) {
                return $videoId;
            }
        }

        return null;
    }

    /**
     * @return list<array{platformId: string, publishAt: DateTimeImmutable}>
     */
    public function getScheduledShortsByChannelId(string $channelId, int $userId): array
    {
        if ($this->googleClientService === null || $this->youtubeServiceFactory === null) {
            throw new RuntimeException('GoogleClientService and YouTubeServiceFactory are required for authenticated operations');
        }

        $client         = $this->googleClientService->buildYouTubeClient($userId);
        $youtubeService = $this->youtubeServiceFactory->create($client);

        $channelResponse = $youtubeService->channels->listChannels('contentDetails', ['id' => $channelId]);
        $channelItems    = $channelResponse->getItems();
        if (empty($channelItems)) {
            return [];
        }

        $uploadsPlaylistId = $channelItems[0]->getContentDetails()->getRelatedPlaylists()->getUploads();
        if (!$uploadsPlaylistId) {
            return [];
        }

        $playlistResponse = $youtubeService->playlistItems->listPlaylistItems('contentDetails', [
            'playlistId' => $uploadsPlaylistId,
            'maxResults' => 50,
        ]);
        $videoIds = array_map(
            static fn ($item) => $item->getContentDetails()->getVideoId(),
            $playlistResponse->getItems()
        );
        if ($videoIds === []) {
            return [];
        }

        $videosResponse = $youtubeService->videos->listVideos('status,contentDetails', [
            'id' => implode(',', $videoIds),
        ]);

        $now       = new DateTimeImmutable();
        $scheduled = [];
        foreach ($videosResponse->getItems() as $item) {
            $status         = $item->getStatus();
            $contentDetails = $item->getContentDetails();

            if ($status === null || $status->getPrivacyStatus() !== 'private') {
                continue;
            }

            $publishAtRaw = $status->getPublishAt();
            if (!$publishAtRaw) {
                continue;
            }

            $publishAt = new DateTimeImmutable($publishAtRaw);
            if ($publishAt <= $now) {
                continue;
            }

            $duration = $this->iso8601ToSeconds($contentDetails->getDuration() ?? 'PT0S');
            if ($duration > 180) {
                continue;
            }

            $scheduled[] = [
                'platformId' => $item->getId(),
                'publishAt'  => $publishAt,
            ];
        }

        return $scheduled;
    }

    private function iso8601ToSeconds(string $duration): int
    {
        $interval = new DateInterval($duration);

        return $interval->h * 3600 + $interval->i * 60 + $interval->s;
    }

    private function createClientWithoutAuth(): Google_Client
    {
        $client = new Google_Client();
        $client->setDeveloperKey($this->apiKey);

        return $client;
    }
}
