<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Voice\Application\UseCases\DeleteVoice\DeleteVoice;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class DeleteVoiceController extends Controller
{
    public function __construct(
        private readonly DeleteVoice $deleteVoice,
    ) {
    }

    public function __invoke(Request $request, string $voiceId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            $this->deleteVoice->execute($voiceId, $user->id);
        } catch (VoiceNotFound $e) {
            return response()->json([
                'error'   => 'Voice not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json(null, 204);
    }
}
