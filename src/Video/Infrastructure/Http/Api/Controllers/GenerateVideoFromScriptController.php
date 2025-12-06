<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Infrastructure\Http\Api\Controllers;

use Canalizador\Script\Domain\Exceptions\ScriptNotFound;
use Canalizador\Video\Application\UseCases\GenerateVideoFromScript\GenerateVideoFromScript;
use Canalizador\Video\Application\UseCases\GenerateVideoFromScript\GenerateVideoFromScriptRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GenerateVideoFromScriptController extends Controller
{
    public function __construct(
        private readonly GenerateVideoFromScript $generateVideoFromScript
    ) {
    }

    /**
     * @throws ScriptNotFound
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'script_id' => 'required|string|uuid',
        ]);

        $generateVideoRequest = new GenerateVideoFromScriptRequest(
            scriptId: $validated['script_id'],
        );

        $response = $this->generateVideoFromScript->execute($generateVideoRequest);

        return response()->json($response->toArray());
    }
}
