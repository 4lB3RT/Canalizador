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
            'local_path'       => 'required|string',
            'base_title'       => 'required|string',
            'base_description' => 'required|string',
        ]);

        return new SmartFragmentAndPublishVideoRequest(
            localPath:       $validated['local_path'],
            baseTitle:       $validated['base_title'],
            baseDescription: $validated['base_description'],
        );
    }
}
