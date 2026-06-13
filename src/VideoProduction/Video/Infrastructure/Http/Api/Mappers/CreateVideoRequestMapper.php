<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Mappers;

use Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo\CreateVideoRequest;
use Illuminate\Http\Request;

final readonly class CreateVideoRequestMapper
{
    public function map(Request $request): CreateVideoRequest
    {
        $validated = $request->validate([
            'video_id' => 'required|string|uuid',
            'script_id' => 'required|string|uuid',
            'category' => 'required|string|in:gaming,meteorology',
            'avatar_id' => 'nullable|string|uuid',
            'resolution' => 'sometimes|string|in:720p,1080p,4k',
            'total_clips' => 'sometimes|integer|min:1|max:8',
            'language' => 'sometimes|string|in:es,en,fr,de,it,pt',
            'model' => 'sometimes|string|in:veo-3.1-lite-generate-preview,veo-3.1-fast-generate-preview,veo-3.0-fast-generate-001,veo-3.1-generate-preview,veo-3.0-generate-001',
        ]);

        $user = $request->user();
        if (!$user) {
            throw new \RuntimeException('User must be authenticated');
        }

        return new CreateVideoRequest(
            videoId: $validated['video_id'],
            userId: $user->id,
            scriptId: $validated['script_id'],
            category: $validated['category'],
            avatarId: $validated['avatar_id'] ?? null,
            resolution: $validated['resolution'] ?? '720p',
            totalClips: (int) ($validated['total_clips'] ?? 5),
            language: $validated['language'] ?? 'es',
            model: $validated['model'] ?? 'veo-3.1-generate-preview',
        );
    }
}
