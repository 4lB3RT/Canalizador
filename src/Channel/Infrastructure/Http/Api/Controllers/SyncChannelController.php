<?php

declare(strict_types=1);

namespace Canalizador\Channel\Infrastructure\Http\Api\Controllers;

use Canalizador\Channel\Application\UseCases\SyncChannel\SyncChannel;
use Canalizador\Channel\Application\UseCases\SyncChannel\SyncChannelRequest;
use Canalizador\Channel\Domain\Exceptions\ChannelNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

final class SyncChannelController extends Controller
{
    public function __construct(
        private readonly SyncChannel $syncChannel,
    ) {
    }

    public function __invoke(Request $request, string $channelId): JsonResponse
    {
        try {
            $this->syncChannel->execute(new SyncChannelRequest(
                channelId: $channelId
            ));

            return response()->json(null, 204);
        } catch (ChannelNotFound $e) {
            return response()->json([
                'error' => 'Channel not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (RuntimeException $e) {
            dd($e);
            return response()->json([
                'error' => 'Failed to sync channel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

