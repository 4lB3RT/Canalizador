<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Voice\Application\UseCases\PreviewVoice\PreviewVoice;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceBlocked;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class PreviewVoiceController extends Controller
{
    public function __construct(
        private readonly PreviewVoice $previewVoice,
    ) {
    }

    public function __invoke(Request $request): BinaryFileResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        $validated = $request->validate([
            'platform_id' => 'required|string',
            'text'        => 'required|string|max:5000',
        ]);

        try {
            $path = $this->previewVoice->execute($validated['platform_id'], $validated['text']);
        } catch (VoiceBlocked $e) {
            return response()->json([
                'error'   => 'Voice blocked',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to preview voice',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->file($path, ['Content-Type' => 'audio/mpeg'])
            ->deleteFileAfterSend(true);
    }
}
