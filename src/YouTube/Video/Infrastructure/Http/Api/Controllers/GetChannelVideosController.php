<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Page;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\Shared\Shared\Domain\ValueObjects\PerPage;
use Canalizador\YouTube\Channel\Application\UseCases\GetChannelVideos\GetChannelVideos;
use Canalizador\YouTube\Channel\Application\UseCases\GetChannelVideos\GetChannelVideosRequest;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Throwable;
use ValueError;

final class GetChannelVideosController extends Controller
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_PER_PAGE = 12;

    public function __construct(
        private readonly GetChannelVideos $getChannelVideos,
    ) {
    }

    public function __invoke(Request $request, string $channelId): JsonResponse
    {
        try {
            $pagination = new Pagination(
                page:    new Page((int) $request->query('page', (string) self::DEFAULT_PAGE)),
                perPage: new PerPage((int) $request->query('per_page', (string) self::DEFAULT_PER_PAGE)),
            );

            $category = $this->resolveCategory($request->query('category'));

            $response = $this->getChannelVideos->execute(new GetChannelVideosRequest(
                channelId:  ChannelId::fromString($channelId),
                userId:     new IntegerId((int) Auth::id()),
                pagination: $pagination,
                category:   $category,
            ));

            return response()->json([
                'data' => $response->videos->map(
                    static fn (Video $video): array => $video->toArray()
                ),
                'meta' => [
                    'page'      => $response->pagination->page()->value(),
                    'per_page'  => $response->pagination->perPage()->value(),
                    'total'     => $response->total->value(),
                    'last_page' => $response->lastPage()->value(),
                ],
            ], 200);
        } catch (ChannelNotFound $e) {
            return response()->json([
                'error'   => 'Channel not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (InvalidArgumentException | ValueError $e) {
            return response()->json([
                'error'   => 'Invalid query parameters',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to list channel videos',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function resolveCategory(mixed $raw): ?Category
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        return Category::from((string) $raw);
    }
}
