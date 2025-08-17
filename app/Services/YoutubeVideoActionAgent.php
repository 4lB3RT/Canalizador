<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\GoogleTokenService;
use Canalizador\Recommendation\Domain\Entities\Recommendation;
use Canalizador\Recommendation\Domain\Entities\RecommendationCollection;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;

final class YoutubeVideoActionAgent
{
    private GoogleTokenService $tokenService;
    private Google_Client $client;
    private Google_Service_YouTube $youtube;

    public function __construct(GoogleTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        $this->client = new Google_Client();
        $this->client->setScopes([
            Google_Service_YouTube::YOUTUBE
        ]);
        $this->client->setAccessType('offline');
        $accessToken = $this->tokenService->getAccessToken();
        if ($accessToken) {
            $this->client->setAccessToken($accessToken);
        }
        $this->youtube = new Google_Service_YouTube($this->client);
    }

    public function applyActions(VideoId $videoId, RecommendationCollection $recommendations): array
    {
        $results = [];
        if (empty($actions)) {
            $actions = [
                [
                    'type' => 'change_title',
                    'value' => 'Nuevo título de ejemplo'
                ],
                [
                    'type' => 'change_description',
                    'value' => 'Nueva descripción de ejemplo'
                ],
                [
                    'type' => 'change_tags',
                    'value' => ['ejemplo', 'video']
                ]
            ];
        }

        foreach ($actions as $action) {
            try {
                switch ($action['type'] ?? null) {
                    case 'change_title':
                        $results[] = $this->changeTitle($videoId, $action['value'] ?? '');
                        break;
                    case 'change_description':
                        $results[] = $this->changeDescription($videoId, $action['value'] ?? '');
                        break;
                    case 'change_tags':
                        $results[] = $this->changeTags($videoId, $action['value'] ?? []);
                        break;
                    default:
                        $results[] = [
                            'success' => false,
                            'error' => 'Tipo de acción no soportado',
                        ];
                }
            } catch (\Throwable $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        return $results;
    }

    private function getVideoSnippet(string $videoId): ?Google_Service_YouTube_VideoSnippet
    {
        $response = $this->youtube->videos->listVideos('snippet', ['id' => $videoId]);
        if (count($response->getItems()) === 0) {
            return null;
        }
        return $response->getItems()[0]->getSnippet();
    }

    private function updateVideoSnippet(string $videoId, callable $modifier): array
    {
        try {
            $video = $this->youtube->videos->listVideos('snippet', ['id' => $videoId])->getItems()[0] ?? null;
            if (!$video) {
                return ['success' => false, 'error' => 'Video no encontrado'];
            }
            $snippet = $video->getSnippet();
            $modifier($snippet);
            $video->setSnippet($snippet);
            $this->youtube->videos->update('snippet', $video);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function changeTitle(string $videoId, string $newTitle): array
    {
        return $this->updateVideoSnippet($videoId, function (Google_Service_YouTube_VideoSnippet $snippet) use ($newTitle) {
            $snippet->setTitle($newTitle);
        });
    }

    private function changeDescription(string $videoId, string $newDescription): array
    {
        return $this->updateVideoSnippet($videoId, function (Google_Service_YouTube_VideoSnippet $snippet) use ($newDescription) {
            $snippet->setDescription($newDescription);
        });
    }

    private function changeTags(string $videoId, array $newTags): array
    {
        return $this->updateVideoSnippet($videoId, function (Google_Service_YouTube_VideoSnippet $snippet) use ($newTags) {
            $snippet->setTags($newTags);
        });
    }
}
