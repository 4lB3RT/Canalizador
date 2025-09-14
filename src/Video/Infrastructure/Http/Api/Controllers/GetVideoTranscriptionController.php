<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\Video\Application\Agent\EditorAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Throwable;

class GetVideoTranscriptionController extends Controller
{
    public function __invoke(string $videoId, EditorAgent $editorAgent): JsonResponse
    {
        if (!$videoId) {
            return response()->json(['error' => 'Missing videoId'], 400);
        }
        try {

            $response = $editorAgent->run($videoId);

            return response()->json(['videoId' => $videoId, 'transcript' => $response]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
