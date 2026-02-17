<?php

declare(strict_types=1);

namespace Canalizador\Voice\Infrastructure\Http\Api\Controllers;

use Canalizador\Voice\Application\UseCases\GenerateVoice\GenerateVoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GenerateVoiceController extends Controller
{
    public function __construct(
        private readonly GenerateVoice $generateVoice,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'avatar_id' => 'required|string',
            'source_audio_path' => 'required|string',
        ]);

        $result = $this->generateVoice->execute(
            avatarId: $request->input('avatar_id'),
            sourceAudioPath: $request->input('source_audio_path'),
        );

        return response()->json($result);
    }
}
