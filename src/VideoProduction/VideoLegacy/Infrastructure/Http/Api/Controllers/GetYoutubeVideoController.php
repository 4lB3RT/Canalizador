<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\VideoLegacy\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\VideoLegacy\Application\UseCases\GetYoutubeVideo;
use Helmreel\VideoProduction\VideoLegacy\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class GetYoutubeVideoController extends Controller
{
    public function __construct(private GetYoutubeVideo $getYoutubeVideo)
    {
    }

    public function __invoke(string $videoId): JsonResponse
    {
        $video = $this->getYoutubeVideo->get(VideoId::fromString($videoId));

        return response()->json($video->toArray());
    }
}
