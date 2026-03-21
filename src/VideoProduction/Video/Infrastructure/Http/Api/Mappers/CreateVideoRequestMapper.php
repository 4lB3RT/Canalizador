<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Mappers;

use Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo\CreateVideoRequest;
use Illuminate\Http\Request;

final readonly class CreateVideoRequestMapper
{
    public function map(Request $request): CreateVideoRequest
    {
        $validated = $request->validate([
            'video_id' => 'required|string|uuid',
            'script_id' => 'required|string|uuid',
            'channel_id' => 'required|string',
            'category' => 'required|string|in:gaming,meteorology',
            'avatar_id' => 'nullable|string|uuid',
        ]);

        return new CreateVideoRequest(
            videoId: $validated['video_id'],
            scriptId: $validated['script_id'],
            channelId: $validated['channel_id'],
            category: $validated['category'],
            avatarId: $validated['avatar_id'] ?? null,
        );
    }
}
