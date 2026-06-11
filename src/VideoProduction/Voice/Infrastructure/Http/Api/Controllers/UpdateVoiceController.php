<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Voice\Application\UseCases\UpdateVoice\UpdateVoice;
use Helmreel\VideoProduction\Voice\Application\UseCases\UpdateVoice\UpdateVoiceRequest;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class UpdateVoiceController extends Controller
{
    public function __construct(
        private readonly UpdateVoice $updateVoice,
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

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'stability' => 'sometimes|numeric|between:0,1',
            'similarity_boost' => 'sometimes|numeric|between:0,1',
            'style' => 'sometimes|numeric|between:0,1',
            'speed' => 'sometimes|numeric|between:0.5,2',
            'use_speaker_boost' => 'sometimes|boolean',
        ]);

        try {
            $result = $this->updateVoice->execute(new UpdateVoiceRequest(
                voiceId: $voiceId,
                userId: $user->id,
                name: $validated['name'] ?? null,
                stability: isset($validated['stability']) ? (float) $validated['stability'] : null,
                similarityBoost: isset($validated['similarity_boost']) ? (float) $validated['similarity_boost'] : null,
                style: isset($validated['style']) ? (float) $validated['style'] : null,
                speed: isset($validated['speed']) ? (float) $validated['speed'] : null,
                useSpeakerBoost: isset($validated['use_speaker_boost']) ? (bool) $validated['use_speaker_boost'] : null,
            ));
        } catch (VoiceNotFound $e) {
            return response()->json([
                'error'   => 'Voice not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json(['data' => $result]);
    }
}
