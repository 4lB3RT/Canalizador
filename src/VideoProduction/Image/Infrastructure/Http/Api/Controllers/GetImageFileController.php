<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Image\Application\UseCases\GetImageFile\GetImageFile;
use Helmreel\VideoProduction\Image\Domain\Exceptions\ImageNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class GetImageFileController extends Controller
{
    public function __construct(
        private readonly GetImageFile $getImageFile,
    ) {
    }

    public function __invoke(Request $request, string $imageId): BinaryFileResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            $path = $this->getImageFile->execute($imageId, $user->id);
        } catch (ImageNotFound $e) {
            return response()->json([
                'error'   => 'Image not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        if (!is_file($path)) {
            return response()->json([
                'error'   => 'Image file missing',
                'message' => 'The image record exists but the file is not available.',
            ], 404);
        }

        return response()->file($path);
    }
}
