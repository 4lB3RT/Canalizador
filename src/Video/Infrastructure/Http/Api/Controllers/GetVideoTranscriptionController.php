<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\Video\Application\Service\VideoTranscriptionService;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Throwable;

class GetVideoTranscriptionController extends Controller
{
    public function __invoke(string $videoId): JsonResponse
    {
        if (!$videoId) {
            return response()->json(['error' => 'Missing videoId'], 400);
        }
        try {
            $service    = new VideoTranscriptionService();
            $transcript = $service->getRelevantSegmentsByTime(new VideoId($videoId));

            return response()->json(['videoId' => $videoId, 'transcript' => $transcript]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
