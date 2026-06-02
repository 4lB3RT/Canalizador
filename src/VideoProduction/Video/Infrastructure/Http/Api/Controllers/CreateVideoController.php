<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo\CreateVideo;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Mappers\CreateVideoRequestMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CreateVideoController extends Controller
{
    public function __construct(
        private readonly CreateVideo $createVideo,
        private readonly CreateVideoRequestMapper $requestMapper
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $createVideoRequest = $this->requestMapper->map($request);

        $response = $this->createVideo->execute($createVideoRequest);

        return response()->json($response->toArray());
    }
}
