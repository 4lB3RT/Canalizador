<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers;

use Helmreel\VideoProduction\Avatar\Application\UseCases\UpdateAvatar\UpdateAvatar;
use Helmreel\VideoProduction\Avatar\Application\UseCases\UpdateAvatar\UpdateAvatarRequest;
use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

final class UpdateAvatarController extends Controller
{
    public function __construct(
        private readonly UpdateAvatar $updateAvatar,
    ) {
    }

    public function __invoke(Request $request, string $avatarId): JsonResponse
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
            'category' => 'sometimes|string|in:gaming,meteorology',
            'presentation_style' => 'sometimes|string|in:energetic,calm,professional,casual',
            'biography' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'voice_id' => 'sometimes|nullable|string',
            'profile_image' => 'sometimes|image|mimes:jpeg,jpg,png|max:20480',
        ]);

        $profileImagePath = null;
        $image = $request->file('profile_image');
        if ($image) {
            $tmpDir = storage_path('tmp');
            if (!File::exists($tmpDir)) {
                File::makeDirectory($tmpDir, 0755, true);
            }
            $filename = uniqid('avatar_', true) . '.' . $image->getClientOriginalExtension();
            $profileImagePath = $tmpDir . '/' . $filename;
            File::put($profileImagePath, File::get($image->getRealPath()));
        }

        $voiceProvided = $request->has('voice_id');
        $voiceValue = $validated['voice_id'] ?? null;
        $clearVoice = $voiceProvided && ($voiceValue === null || $voiceValue === '');

        try {
            $result = $this->updateAvatar->execute(new UpdateAvatarRequest(
                avatarId: $avatarId,
                userId: $user->id,
                name: $validated['name'] ?? null,
                category: $validated['category'] ?? null,
                presentationStyle: $validated['presentation_style'] ?? null,
                biography: $validated['biography'] ?? null,
                description: $validated['description'] ?? null,
                voiceId: $clearVoice ? null : $voiceValue,
                clearVoice: $clearVoice,
                profileImagePath: $profileImagePath,
            ));
        } catch (AvatarNotFound $e) {
            return response()->json([
                'error'   => 'Avatar not found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json(['data' => $result]);
    }
}
