<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\YouTube\Video\Application\UseCases\FragmentAndPublishVideo\FragmentAndPublishVideo;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers\FragmentAndPublishVideoRequestMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class FragmentAndPublishVideoController extends Controller
{
    public function __construct(
        private readonly FragmentAndPublishVideo $fragmentAndPublishVideo,
        private readonly FragmentAndPublishVideoRequestMapper $requestMapper,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $fragmentRequest = $this->requestMapper->map($request);

        try {
            $response = $this->fragmentAndPublishVideo->execute($fragmentRequest);

            return response()->json($response->toArray(), 201);
        } catch (VideoFragmentationFailed $e) {
            return response()->json([
                'error'   => 'Video fragmentation failed',
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
