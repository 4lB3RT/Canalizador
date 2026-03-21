<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers;

use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideoRequest;
use Illuminate\Http\Request;

final readonly class PublishVideoRequestMapper
{
    public function map(Request $request): PublishVideoRequest
    {
        $validated = $request->validate([
            'video_id' => 'required|string|uuid',
            'platform' => 'required|string|in:youtube',
        ]);

        return new PublishVideoRequest(
            videoId:  $validated['video_id'],
            platform: $validated['platform']
        );
    }
}
