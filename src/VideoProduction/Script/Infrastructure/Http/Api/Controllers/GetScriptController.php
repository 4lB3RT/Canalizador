<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Script\Application\UseCases\GetScript\GetScript;
use Helmreel\VideoProduction\Script\Domain\Exceptions\ScriptNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetScriptController extends Controller
{
    public function __construct(
        private readonly GetScript $getScript,
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
            $script = $this->getScript->execute($scriptId, $user->id);
        } catch (ScriptNotFound $e) {
            return response()->json([
                'error'   => 'Script not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json(['data' => $script]);
    }
}
