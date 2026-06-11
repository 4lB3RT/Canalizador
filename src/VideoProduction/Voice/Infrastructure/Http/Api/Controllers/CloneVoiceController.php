<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Voice\Application\UseCases\CloneVoice\CloneVoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

final class CloneVoiceController extends Controller
{
    public function __construct(
        private readonly CloneVoice $cloneVoice,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'audio' => 'required|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a|max:51200',
        ]);

        $audio = $request->file('audio');

        $tmpDir = storage_path('tmp');
        if (!File::exists($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }

        $filename = uniqid('voice_', true) . '.' . $audio->getClientOriginalExtension();
        $audioPath = $tmpDir . '/' . $filename;
        File::put($audioPath, File::get($audio->getRealPath()));

        $result = $this->cloneVoice->execute(
            audioPath: $audioPath,
            name: $validated['name'],
            userId: $user->id,
        );

        return response()->json(['data' => $result]);
    }
}
