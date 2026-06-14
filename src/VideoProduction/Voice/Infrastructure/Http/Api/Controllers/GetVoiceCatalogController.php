<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Voice\Application\UseCases\GetVoiceCatalog\GetVoiceCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

final class GetVoiceCatalogController extends Controller
{
    public function __construct(
        private readonly GetVoiceCatalog $getVoiceCatalog,
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

        try {
            return response()->json([
                'data' => $this->getVoiceCatalog->execute(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to load voice catalog',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
