<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use Canalizador\YouTube\Channel\Application\UseCases\UpdateChannelWithAI\UpdateChannelWithAI;
use Canalizador\YouTube\Channel\Application\UseCases\UpdateChannelWithAI\UpdateChannelWithAIRequest;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

final class UpdateChannelWithAIController extends Controller
{
    public function __construct(
        private readonly UpdateChannelWithAI $updateChannelWithAI,
    ) {
    }

    public function __invoke(Request $request, string $channelId): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User must be authenticated',
                ], 401);
            }

            $this->updateChannelWithAI->execute(new UpdateChannelWithAIRequest(
                channelId: $channelId,
                userId: $user->id
            ));

            return response()->json(null, 204);
        } catch (ChannelNotFound $e) {
            return response()->json([
                'error' => 'Channel not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => 'Failed to update channel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

