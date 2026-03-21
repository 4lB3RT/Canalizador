<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Infrastructure\Http\Api\Mappers;

use Canalizador\VideoProduction\Avatar\Application\UseCases\CreateAvatar\CreateAvatarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

final readonly class CreateAvatarRequestMapper
{
    public function map(Request $request): CreateAvatarRequest
    {
        try {
            $validated = $request->validate([
                'avatar_id' => 'required|string|uuid',
                'name' => 'required|string|max:100',
                'profile_image' => 'required|image|mimes:jpeg,jpg,png|max:20480',
                'biography' => 'nullable|string',
                'presentation_style' => 'required|string|in:energetic,calm,professional,casual',
                'category' => 'nullable|string|in:gaming,meteorology',
                'voice_id' => 'nullable|string',
            ]);
        }catch (\Throwable $exception)
        {
            dd($exception);
        }

        $user = $request->user();
        if (!$user) {
            throw new \RuntimeException('User must be authenticated');
        }

        $image = $request->file('profile_image');
        if (!$image) {
            throw new \RuntimeException('Profile image is required');
        }

        $tmpDir = storage_path('tmp');
        if (!File::exists($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }

        $filename = uniqid('avatar_', true) . '.' . $image->getClientOriginalExtension();
        $fullImagePath = $tmpDir . '/' . $filename;
        File::put($fullImagePath, File::get($image->getRealPath()));

        return new CreateAvatarRequest(
            avatarId: $validated['avatar_id'],
            userId: $user->id,
            name: $validated['name'],
            profileImagePath: $fullImagePath,
            biography: $validated['biography'] ?? '',
            presentationStyle: $validated['presentation_style'],
            category: $validated['category'] ?? 'gaming',
            voiceId: $validated['voice_id'] ?? null,
        );
    }
}

