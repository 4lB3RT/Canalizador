<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers;

use Canalizador\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo\SmartFragmentAndPublishVideoRequest;
use Illuminate\Http\Request;

final readonly class SmartFragmentAndPublishVideoRequestMapper
{
    public function map(Request $request): SmartFragmentAndPublishVideoRequest
    {
        $validated = $request->validate([
            'video_id'         => 'required|string',
            'local_path'       => 'required|string',
            'base_title'       => 'required|string',
            'base_description' => 'required|string',
        ]);

        return new SmartFragmentAndPublishVideoRequest(
            videoId:         $validated['video_id'],
            localPath:       $validated['local_path'],
            baseTitle:       $validated['base_title'],
            baseDescription: $validated['base_description'],
        );
    }
}
