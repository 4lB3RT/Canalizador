<?php

declare(strict_types=1);

namespace Canalizador\News\Infrastructure\Http\Api\Controllers;

use Canalizador\News\Application\UseCases\DownloadNews\DownloadNews;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class DownloadNewsController extends Controller
{
    public function __construct(
        private readonly DownloadNews $downloadNews,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $newsList = $this->downloadNews->execute();

        return response()->json(
            array_map(fn ($news) => $news->toArray(), $newsList)
        );
    }
}
