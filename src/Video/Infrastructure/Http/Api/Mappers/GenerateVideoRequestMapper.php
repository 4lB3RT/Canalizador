<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Http\Api\Mappers;

use Canalizador\Video\Application\UseCases\GenerateVideo\GenerateVideoRequest;
use Illuminate\Http\Request;

final readonly class GenerateVideoRequestMapper
{
    public function map(Request $request): GenerateVideoRequest
    {
        $validated = $request->validate([
            'video_id' => 'required|string|uuid',
            'script_id' => 'required|string|uuid',
            'channel_id' => 'required|string',
            'category' => 'required|string|in:gaming,astrology',
            'avatar_id' => 'nullable|string|uuid',
            'prompt' => 'nullable|string',
        ]);

        return new GenerateVideoRequest(
            videoId: $validated['video_id'],
            scriptId: $validated['script_id'],
            channelId: $validated['channel_id'],
            category: $validated['category'],
            avatarId: $validated['avatar_id'] ?? null,
            prompt: $validated['prompt'] ?? null,
        );
    }
}
