<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use Canalizador\YouTube\Channel\Application\UseCases\RegisterChannel\RegisterChannel;
use Canalizador\YouTube\Channel\Application\UseCases\RegisterChannel\RegisterChannelRequest;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

final class RegisterChannelController extends Controller
{
    public function __construct(
        private readonly RegisterChannel $registerChannel,
    ) {
    }

    public function __invoke(Request $request, string $channelId): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'User must be authenticated',
                ], 401);
            }

            $this->registerChannel->execute(new RegisterChannelRequest(
                channelId: $channelId,
                userId:    $user->id,
            ));

            return response()->json(null, 204);
        } catch (ChannelNotFound $e) {
            return response()->json([
                'error'   => 'Channel not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (RuntimeException $e) {
            return response()->json([
                'error'   => 'Failed to register channel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
