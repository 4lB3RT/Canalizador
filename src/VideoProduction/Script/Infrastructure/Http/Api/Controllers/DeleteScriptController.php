<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Script\Application\UseCases\DeleteScript\DeleteScript;
use Helmreel\VideoProduction\Script\Domain\Exceptions\ScriptNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class DeleteScriptController extends Controller
{
    public function __construct(
        private readonly DeleteScript $deleteScript,
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

        try {
            $this->deleteScript->execute($scriptId, $user->id);
        } catch (ScriptNotFound $e) {
            return response()->json([
                'error'   => 'Script not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json(null, 204);
    }
}
