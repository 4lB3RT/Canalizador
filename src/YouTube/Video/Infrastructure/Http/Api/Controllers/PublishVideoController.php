<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoLocalPathNotSet;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\PublishVideoRequestMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class PublishVideoController extends Controller
{
    public function __construct(
        private readonly PublishVideo $publishVideo,
        private readonly PublishVideoRequestMapper $requestMapper
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $publishVideoRequest = $this->requestMapper->map($request);

        try {
            $response = $this->publishVideo->execute($publishVideoRequest);

            return response()->json($response->toArray(), 201);
        } catch (VideoNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (VideoLocalPathNotSet $e) {
            return response()->json([
                'error'   => 'Video local path not set',
                'message' => $e->getMessage(),
            ], 400);
        } catch (YouTubeOperationFailed $e) {
            return response()->json([
                'error'   => 'Failed to publish video',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
