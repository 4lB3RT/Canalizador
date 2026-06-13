<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Voice\Application\UseCases\GenerateVoice\GenerateVoice;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceBlocked;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class GenerateVoiceController extends Controller
{
    public function __construct(
        private readonly GenerateVoice $generateVoice,
    ) {
    }

    public function __invoke(Request $request, string $voiceId): BinaryFileResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        $validated = $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        try {
            $path = $this->generateVoice->execute($voiceId, $validated['text'], $user->id);
        } catch (VoiceNotFound $e) {
            return response()->json([
                'error'   => 'Voice not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (VoiceBlocked $e) {
            return response()->json([
                'error'   => 'Voice blocked',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->file($path, ['Content-Type' => 'audio/mpeg'])
            ->deleteFileAfterSend(true);
    }
}
