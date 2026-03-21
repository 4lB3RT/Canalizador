<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers;

use Canalizador\VideoProduction\Avatar\Application\UseCases\UpdateAvatar\UpdateAvatar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class UpdateAvatarController extends Controller
{
    public function __construct(
        private readonly UpdateAvatar $updateAvatar,
    ) {
    }

    public function __invoke(Request $request, string $avatarId): JsonResponse
    {
        $request->validate([
            'voice_id' => 'nullable|string',
            'biography' => 'nullable|string',
            'presentation_style' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $result = $this->updateAvatar->execute(
            avatarId: $avatarId,
            voiceId: $request->input('voice_id'),
            biography: $request->input('biography'),
            presentationStyle: $request->input('presentation_style'),
            description: $request->input('description'),
        );

        return response()->json($result);
    }
}
