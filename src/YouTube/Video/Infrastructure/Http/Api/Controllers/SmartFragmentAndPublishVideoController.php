<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo\SmartFragmentAndPublishVideo;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\SmartFragmentAndPublishVideoRequestMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class SmartFragmentAndPublishVideoController extends Controller
{
    public function __construct(
        private readonly SmartFragmentAndPublishVideo $smartFragmentAndPublishVideo,
        private readonly SmartFragmentAndPublishVideoRequestMapper $requestMapper,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $smartRequest = $this->requestMapper->map($request);

        try {
            $response = $this->smartFragmentAndPublishVideo->execute($smartRequest);

            return response()->json($response->toArray(), 201);
        } catch (VideoNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (VideoFragmentationFailed $e) {
            return response()->json([
                'error'   => 'Smart video fragmentation failed',
                'message' => $e->getMessage(),
            ], 500);
        } catch (YouTubeOperationFailed $e) {
            return response()->json([
                'error'   => 'Failed to publish video fragment',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
