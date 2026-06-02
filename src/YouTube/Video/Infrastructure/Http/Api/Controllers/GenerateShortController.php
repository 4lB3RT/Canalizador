<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\YouTube\Video\Application\UseCases\GenerateShort\GenerateShort;
use Helmreel\YouTube\Video\Application\UseCases\GenerateShort\GenerateShortRequest;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GenerateShortController extends Controller
{
    public function __construct(
        private readonly GenerateShort $generateShort,
    ) {
    }

    public function __invoke(string $videoYoutubeId, Request $request): JsonResponse
    {
        try {
            $this->generateShort->execute(
                new GenerateShortRequest(videoId: $videoYoutubeId)
            );

            return response()->json(null, 204);
        } catch (VideoNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
