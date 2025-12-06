<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Infrastructure\Http\Api\Controllers;

use Canalizador\Video\Application\UseCases\RetrieveVideoContent;
use Canalizador\Video\Application\UseCases\RetrieveVideoContentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class RetrieveVideoContentController extends Controller
{
    public function __construct(
        private readonly RetrieveVideoContent $retrieveVideoContent
    ) {
    }

    public function __invoke(Request $request, string $videoId): JsonResponse
    {
        $retrieveRequest = new RetrieveVideoContentRequest(
            videoId: $videoId,
        );

        $response = $this->retrieveVideoContent->execute($retrieveRequest);

        return response()->json($response->toArray());
    }
}
