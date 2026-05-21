<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Page;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\Shared\Shared\Domain\ValueObjects\PerPage;
use Canalizador\YouTube\Channel\Application\UseCases\GetChannels\GetChannels;
use Canalizador\YouTube\Channel\Application\UseCases\GetChannels\GetChannelsRequest;
use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Throwable;

final class GetChannelsController extends Controller
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_PER_PAGE = 12;

    public function __construct(
        private readonly GetChannels $getChannels,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $pagination = new Pagination(
                page:    new Page((int) $request->query('page', (string) self::DEFAULT_PAGE)),
                perPage: new PerPage((int) $request->query('per_page', (string) self::DEFAULT_PER_PAGE)),
            );

            $response = $this->getChannels->execute(new GetChannelsRequest(
                userId:     new IntegerId((int) Auth::id()),
                pagination: $pagination,
            ));

            return response()->json([
                'data' => $response->channels->map(
                    static fn (Channel $channel): array => $channel->toArray()
                ),
                'meta' => [
                    'page'      => $response->pagination->page()->value(),
                    'per_page'  => $response->pagination->perPage()->value(),
                    'total'     => $response->total->value(),
                    'last_page' => $response->lastPage()->value(),
                ],
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error'   => 'Invalid pagination',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to list channels',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
