<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Video\Application\UseCases\ApplyVoice\ApplyVoice;
use Helmreel\VideoProduction\Video\Application\UseCases\ApplyVoice\ApplyVoiceRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ApplyVoiceController extends Controller
{
    public function __construct(
        private readonly ApplyVoice $applyVoice,
    ) {
    }

    public function __invoke(Request $request, string $videoId): BinaryFileResponse
    {
        $request->validate([
            'avatar_id' => 'required|string',
        ]);

        $outputPath = $this->applyVoice->execute(
            new ApplyVoiceRequest(
                videoId: $videoId,
                avatarId: $request->input('avatar_id'),
            )
        );

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}
