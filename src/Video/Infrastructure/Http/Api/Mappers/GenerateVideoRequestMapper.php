<?php

declare(strict_types = 1);

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
            'prompt' => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        return new GenerateVideoRequest(
            videoId: $validated['video_id'],
            scriptId: $validated['script_id'],
            prompt: $validated['prompt'] ?? null,
            title: $validated['title'] ?? null,
        );
    }
}
