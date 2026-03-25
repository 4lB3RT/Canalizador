<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Voice\Infrastructure\Http\Api\Controllers;

use Canalizador\VideoProduction\Voice\Application\UseCases\CloneVoice\CloneVoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CloneVoiceController extends Controller
{
    public function __construct(
        private readonly CloneVoice $cloneVoice,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'audio_path' => 'required|string',
            'name' => 'required|string',
        ]);

        $result = $this->cloneVoice->execute(
            audioPath: $request->input('audio_path'),
            name: $request->input('name'),
        );

        return response()->json($result);
    }
}
