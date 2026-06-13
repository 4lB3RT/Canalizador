<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Script\Application\UseCases\CreateScript\CreateScript;
use Helmreel\VideoProduction\Script\Application\UseCases\CreateScript\CreateScriptRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

final class CreateScriptController extends Controller
{
    public function __construct(
        private readonly CreateScript $createScript,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        $validated = $request->validate([
            'script_id' => 'required|string|uuid',
            'category' => 'required|string|in:gaming,meteorology',
            'total_clips' => 'sometimes|integer|min:1|max:8',
            'title' => 'sometimes|nullable|string|max:255',
        ]);

        try {
            $script = $this->createScript->execute(new CreateScriptRequest(
                scriptId: $validated['script_id'],
                userId: $user->id,
                category: $validated['category'],
                totalClips: (int) ($validated['total_clips'] ?? 5),
                title: $validated['title'] ?? null,
            ));
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to create script',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['data' => $script], 201);
    }
}
