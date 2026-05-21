<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Application\UseCases\GetChannel\GetChannel;
use Canalizador\YouTube\Channel\Application\UseCases\GetChannel\GetChannelRequest;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Throwable;

final class GetChannelController extends Controller
{
    public function __construct(
        private readonly GetChannel $getChannel,
    ) {
    }

    public function __invoke(Request $request, string $channelId): JsonResponse
    {
        try {
            $channel = $this->getChannel->execute(new GetChannelRequest(
                channelId: ChannelId::fromString($channelId),
                userId:    new IntegerId((int) Auth::id()),
            ));

            return response()->json(['data' => $channel->toArray()], 200);
        } catch (ChannelNotFound $e) {
            return response()->json([
                'error'   => 'Channel not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to fetch channel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
