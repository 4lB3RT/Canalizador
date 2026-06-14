<?php

declare(strict_types=1);

namespace Helmreel\Shared\Media\Infrastructure\Http\Api\Controllers;

use Helmreel\Shared\Media\Application\UseCases\GetMediaFile\GetMediaFile;
use Helmreel\Shared\Media\Domain\Exceptions\MediaNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class GetMediaFileController extends Controller
{
    public function __construct(
        private readonly GetMediaFile $getMediaFile,
    ) {
    }

    public function __invoke(Request $request, string $mediaId): BinaryFileResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            $media = $this->getMediaFile->execute($mediaId, $user->id);
        } catch (MediaNotFound $e) {
            return response()->json([
                'error'   => 'Media not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        $path = $media->path()->value();
        if (!is_file($path)) {
            return response()->json([
                'error'   => 'Media file missing',
                'message' => 'The media record exists but the file is not available.',
            ], 404);
        }

        return response()->file($path, ['Content-Type' => $media->type()->mimeType()]);
    }
}
