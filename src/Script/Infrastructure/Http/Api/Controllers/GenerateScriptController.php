<?php

declare(strict_types = 1);

namespace Canalizador\Script\Infrastructure\Http\Api\Controllers;

use Canalizador\Script\Application\UseCases\GenerateScript;
use Canalizador\Script\Application\UseCases\GenerateScriptRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GenerateScriptController extends Controller
{
    public function __construct(
        private GenerateScript $generateScript
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuid' => 'required|string|uuid',
            'prompt' => 'nullable|string',
        ]);

        $generateScriptRequest = new GenerateScriptRequest(
            uuid: $validated['uuid'],
            prompt: $validated['prompt'] ?? null
        );

        $response = $this->generateScript->execute($generateScriptRequest);

        return response()->json($response->toArray());
    }
}
