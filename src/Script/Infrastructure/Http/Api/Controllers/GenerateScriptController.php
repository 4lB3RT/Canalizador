<?php

declare(strict_types = 1);

namespace Canalizador\Script\Infrastructure\Http\Api\Controllers;

use Canalizador\Script\Domain\Services\GenerateScript;
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

        $script = $this->generateScript->generate(
            scriptId: $validated['uuid'],
            prompt: $validated['prompt'] ?? null
        );

        return response()->json($script->toArray());
    }
}
