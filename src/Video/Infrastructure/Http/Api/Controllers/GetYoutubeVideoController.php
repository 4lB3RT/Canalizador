<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Http\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Canalizador\Video\Application\UseCases\GetYoutubeVideo;
use Canalizador\Video\Domain\ValueObjects\VideoId;

class GetYoutubeVideoController extends Controller
{
    public function __construct(private GetYoutubeVideo $getYoutubeVideo)
    {
    }

    public function __invoke(string $id): JsonResponse
    {
        $video = $this->getYoutubeVideo->get(new VideoId($id));

        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        return response()->json([
            'id'          => $video->id()->value(),
            'title'       => $video->title()->value(),
            'publishedAt' => $video->publishedAt()->value(),
            'category'    => $video->category()->name()->value(),
        ]);
    }
}
