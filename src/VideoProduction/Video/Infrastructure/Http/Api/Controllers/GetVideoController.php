<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Video\Application\UseCases\GetVideo\GetVideo;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetVideoController extends Controller
{
    public function __construct(
        private readonly GetVideo $getVideo,
    ) {
    }

    public function __invoke(Request $request, string $videoId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            $video = $this->getVideo->execute($videoId, $user->id);
        } catch (VideoNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'data' => $video,
        ]);
    }
}
