<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Avatar\Application\UseCases\GenerateAvatarMetadata\GenerateAvatarMetadata;
use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

final class GenerateAvatarMetadataController extends Controller
{
    public function __construct(
        private readonly GenerateAvatarMetadata $generateAvatarMetadata,
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
            $avatar = $this->generateAvatarMetadata->execute($avatarId, $user->id);
        } catch (AvatarNotFound $e) {
            return response()->json([
                'error'   => 'Avatar not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to generate avatar metadata',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $avatar,
        ]);
    }
}
