<?php

declare(strict_types = 1);

namespace Canalizador\Shared\Infrastructure\ClientAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class YoutubeDataApiClient
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl = 'https://www.googleapis.com/youtube/v3/';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 5.0,
        ]);
    }

    public function getVideoById(string $videoId): ?array
    {
        try {
            $response = $this->client->get('videos', [
                'query' => [
                    'id'   => $videoId,
                    'part' => 'snippet,contentDetails,statistics',
                    'key'  => $this->apiKey,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['items'][0] ?? null;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
