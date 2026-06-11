<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Avatar\Application\UseCases\DeleteAvatar\DeleteAvatar;
use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class DeleteAvatarController extends Controller
{
    public function __construct(
        private readonly DeleteAvatar $deleteAvatar,
    ) {
    }

    public function __invoke(Request $request, string $avatarId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            $this->deleteAvatar->execute($avatarId, $user->id);
        } catch (AvatarNotFound $e) {
            return response()->json([
                'error'   => 'Avatar not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json(null, 204);
    }
}
