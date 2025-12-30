<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\Video\Application\UseCases\GenerateVideo\GenerateVideo;
use Canalizador\Video\Infrastructure\Http\Api\Mappers\GenerateVideoRequestMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GenerateVideoController extends Controller
{
    public function __construct(
        private readonly GenerateVideo $generateVideo,
        private readonly GenerateVideoRequestMapper $requestMapper
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $generateVideoRequest = $this->requestMapper->map($request);

        $response = $this->generateVideo->execute($generateVideoRequest);

        return response()->json($response->toArray());
    }
}
