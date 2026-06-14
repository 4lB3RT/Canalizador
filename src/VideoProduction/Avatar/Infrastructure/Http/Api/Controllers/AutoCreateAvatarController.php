<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Avatar\Application\UseCases\AutoCreateAvatar\AutoCreateAvatar;
use Helmreel\VideoProduction\Avatar\Application\UseCases\AutoCreateAvatar\AutoCreateAvatarRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Throwable;

final class AutoCreateAvatarController extends Controller
{
    public function __construct(
        private readonly AutoCreateAvatar $autoCreateAvatar,
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
            $avatar = $this->autoCreateAvatar->execute(
                new AutoCreateAvatarRequest(
                    avatarId: Str::uuid()->toString(),
                    userId: $user->id,
                ),
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to auto-create avatar',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $avatar,
        ], 201);
    }
}
