<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\Video\Application\UseCases\GenerateVideo\GenerateVideo;
use Canalizador\Video\Application\UseCases\GenerateVideo\GenerateVideoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GenerateVideoController extends Controller
{
    public function __construct(
        private readonly GenerateVideo $generateVideo
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video_id' => 'required|string|uuid',
            'script_id' => 'required|string|uuid',
            'prompt' => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        $generateVideoRequest = new GenerateVideoRequest(
            videoId: $validated['video_id'],
            scriptId: $validated['script_id'],
            prompt: $validated['prompt'] ?? null,
            title: $validated['title'] ?? null,
        );

        $response = $this->generateVideo->execute($generateVideoRequest);

        return response()->json($response->toArray());
    }
}
