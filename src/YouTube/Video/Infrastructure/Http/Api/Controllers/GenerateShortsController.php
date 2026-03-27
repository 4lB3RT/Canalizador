<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\YouTube\Video\Application\UseCases\GenerateShorts\GenerateShorts;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShorts\GenerateShortsRequest;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GenerateShortsController extends Controller
{
    public function __construct(
        private readonly GenerateShorts $generateShorts,
    ) {
    }

    public function __invoke(string $videoYoutubeId, Request $request): JsonResponse
    {
        try {
            $response = $this->generateShorts->execute(
                new GenerateShortsRequest(videoYoutubeId: $videoYoutubeId)
            );

            return response()->json($response->toArray(), 201);
        } catch (VideoNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (YouTubeOperationFailed $e) {
            return response()->json([
                'error'   => 'YouTube operation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
