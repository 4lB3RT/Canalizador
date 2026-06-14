<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\Shared\Media\Domain\Exceptions\MediaNotFound;
use Helmreel\YouTube\Video\Application\UseCases\PublishProductionVideo\PublishProductionVideo;
use Helmreel\YouTube\Video\Domain\Exceptions\PublishAtRequired;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Mappers\PublishProductionVideoRequestMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class PublishProductionVideoController extends Controller
{
    public function __construct(
        private readonly PublishProductionVideo $publishProductionVideo,
        private readonly PublishProductionVideoRequestMapper $requestMapper,
    ) {
    }

    public function __invoke(Request $request, string $videoId): JsonResponse
    {
        $publishRequest = $this->requestMapper->map($request, $videoId);

        try {
            $response = $this->publishProductionVideo->execute($publishRequest);

            return response()->json($response->toArray(), 201);
        } catch (MediaNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (PublishAtRequired $e) {
            return response()->json([
                'error'   => 'Invalid publish request',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error'   => 'Failed to publish video',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
