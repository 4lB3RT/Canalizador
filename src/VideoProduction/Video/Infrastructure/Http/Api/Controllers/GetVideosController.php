<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Video\Application\UseCases\GetVideos\GetVideos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetVideosController extends Controller
{
    public function __construct(
        private readonly GetVideos $getVideos,
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
            'data' => $this->getVideos->execute($user->id),
        ]);
    }
}
