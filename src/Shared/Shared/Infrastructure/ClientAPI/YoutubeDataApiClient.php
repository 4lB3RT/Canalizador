<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Infrastructure\ClientAPI;

use App\Services\GoogleClientService;
use Canalizador\VideoProduction\Video\Domain\Services\YouTubeServiceFactory;
use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Google\Service\Exception;
use Google_Client;
use Google_Service_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_Channel;
use Google_Service_YouTube_ChannelBrandingSettings;
use Google_Service_YouTube_ChannelSettings;
use RuntimeException;

final class YoutubeDataApiClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly ?GoogleClientService $googleClientService = null,
        private readonly ?YouTubeServiceFactory $youtubeServiceFactory = null,
    ) {
    }

    /**
     * @throws Google_Service_Exception
     */
    public function getChannelById(string $channelId): ?array
    {
        $client = $this->createClientWithoutAuth();
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
            $client = $this->createClientWithoutAuth();
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

        $channelId = $channel->id()->value();
        $existingData = $this->getChannelById($channelId);
        if (!$existingData) {
            throw new RuntimeException('Channel not found on YouTube');
        }

        $client = $this->googleClientService->buildYouTubeClient();
        $youtubeService = $this->youtubeServiceFactory->create($client);

        $response = $youtubeService->channels->listChannels('snippet,brandingSettings', ['id' => $channelId]);
        $items = $response->getItems();

        if (empty($items)) {
            throw new RuntimeException('Channel not found on YouTube');
        }

        $existingChannel = $items[0];
        $channelSnippet = $existingChannel->getSnippet();
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

    private function createClientWithoutAuth(): Google_Client
    {
        $client = new Google_Client();
        $client->setDeveloperKey($this->apiKey);

        return $client;
    }
}
