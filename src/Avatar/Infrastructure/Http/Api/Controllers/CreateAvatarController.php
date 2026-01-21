<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Infrastructure\Http\Api\Controllers;

use Canalizador\Avatar\Application\UseCases\CreateAvatar\CreateAvatar;
use Canalizador\Avatar\Infrastructure\Http\Api\Mappers\CreateAvatarRequestMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CreateAvatarController extends Controller
{
    public function __construct(
        private readonly CreateAvatar $createAvatar,
        private readonly CreateAvatarRequestMapper $requestMapper
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $createAvatarRequest = $this->requestMapper->map($request);

        $this->createAvatar->execute($createAvatarRequest);

        return response()->json([], 200);
    }
}

