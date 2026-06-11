<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Script\Application\UseCases\GetScripts\GetScripts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetScriptsController extends Controller
{
    public function __construct(
        private readonly GetScripts $getScripts,
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

        return response()->json([
            'data' => $this->getScripts->execute($user->id),
        ]);
    }
}
