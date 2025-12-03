<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\Video\Application\UseCases\GetYoutubeVideo;
use Canalizador\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\Video\Domain\ValueObjects\VideoId;
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
