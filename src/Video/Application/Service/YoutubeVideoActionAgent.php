<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\Service;

use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;

class YoutubeVideoActionAgent
{
    public function __construct(private YoutubeDataApiClient $youtubeDataApiClient) {}

    public function applyActions(array $actions, string $videoId): array
    {
        $results = [];
        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'change_title':
                    $results[] = ['type' => 'change_title', 'status' => 'pending', 'message' => 'Implement updateTitle logic'];
                    break;
                case 'change_description':
                    $results[] = ['type' => 'change_description', 'status' => 'pending', 'message' => 'Implement updateDescription logic'];
                    break;
                case 'change_tags':
                    $results[] = ['type' => 'change_tags', 'status' => 'pending', 'message' => 'Implement updateTags logic'];
                    break;
                case 'change_thumbnail':
                    $results[] = ['type' => 'change_thumbnail', 'status' => 'pending', 'message' => 'Implement updateThumbnail logic'];
                    break;
                default:
                    $results[] = ['type' => $action['type'], 'status' => 'error', 'message' => 'Unknown action type'];
            }
        }
        return $results;
    }
}
