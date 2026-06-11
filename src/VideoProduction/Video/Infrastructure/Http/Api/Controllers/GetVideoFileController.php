<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Video\Application\UseCases\GetVideoFile\GetVideoFile;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class GetVideoFileController extends Controller
{
    public function __construct(
        private readonly GetVideoFile $getVideoFile,
    ) {
    }

    public function __invoke(Request $request, string $videoId): BinaryFileResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            $path = $this->getVideoFile->execute($videoId, $user->id);
        } catch (VideoNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Video not ready',
                'message' => $e->getMessage(),
            ], 409);
        }

        if (!is_file($path)) {
            return response()->json([
                'error'   => 'Video file missing',
                'message' => 'The video record is completed but the file is not available.',
            ], 404);
        }

        return response()->file($path, ['Content-Type' => 'video/mp4']);
    }
}
