<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Script\Application\UseCases\UpdateScript\UpdateScript;
use Helmreel\VideoProduction\Script\Domain\Exceptions\ScriptNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class UpdateScriptController extends Controller
{
    public function __construct(
        private readonly UpdateScript $updateScript,
    ) {
    }

    public function __invoke(Request $request, string $scriptId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        try {
            $script = $this->updateScript->execute(
                scriptId: $scriptId,
                userId: $user->id,
                title: $validated['title'] ?? null,
                content: $validated['content'] ?? null,
            );
        } catch (ScriptNotFound $e) {
            return response()->json([
                'error'   => 'Script not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json(['data' => $script]);
    }
}
