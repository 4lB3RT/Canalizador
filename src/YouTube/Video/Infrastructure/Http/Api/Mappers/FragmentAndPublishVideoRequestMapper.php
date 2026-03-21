<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Mappers;

use Canalizador\YouTube\Video\Application\UseCases\FragmentAndPublishVideo\FragmentAndPublishVideoRequest;
use Illuminate\Http\Request;

final readonly class FragmentAndPublishVideoRequestMapper
{
    public function map(Request $request): FragmentAndPublishVideoRequest
    {
        $validated = $request->validate([
            'local_path'               => 'required|string',
            'base_title'               => 'required|string',
            'base_description'         => 'required|string',
            'segment_duration_seconds' => 'sometimes|integer|min:1',
        ]);

        return new FragmentAndPublishVideoRequest(
            localPath:               $validated['local_path'],
            baseTitle:               $validated['base_title'],
            baseDescription:         $validated['base_description'],
            segmentDurationSeconds:  $validated['segment_duration_seconds'] ?? 60,
        );
    }
}
