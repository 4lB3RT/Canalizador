<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Infrastructure\Http\Api\Mappers;

use Helmreel\YouTube\Video\Application\UseCases\PublishProductionVideo\PublishProductionVideoRequest;
use Illuminate\Http\Request;

final readonly class PublishProductionVideoRequestMapper
{
    public function map(Request $request, string $videoId): PublishProductionVideoRequest
    {
        $validated = $request->validate([
            'channel_id'  => 'required|string',
            'privacy'     => 'required|string|in:public,private,unlisted,scheduled',
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'media_id'    => 'required|string|uuid',
            'publish_at'  => 'required_if:privacy,scheduled|nullable|date|after:now',
        ]);

        return new PublishProductionVideoRequest(
            videoId: $videoId,
            channelId: $validated['channel_id'],
            privacy: $validated['privacy'],
            title: $validated['title'],
            description: $validated['description'] ?? '',
            mediaId: $validated['media_id'],
            publishAt: $validated['publish_at'] ?? null,
        );
    }
}
