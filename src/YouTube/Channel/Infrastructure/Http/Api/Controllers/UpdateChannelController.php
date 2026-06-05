<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\YouTube\Channel\Application\UseCases\UpdateChannel\UpdateChannel;
use Helmreel\YouTube\Channel\Application\UseCases\UpdateChannel\UpdateChannelRequest;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

final class UpdateChannelController extends Controller
{
    public function __construct(
        private readonly UpdateChannel $updateChannel,
    ) {
    }

    public function __invoke(Request $request, string $channelId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'auto_sync'    => ['sometimes', 'boolean'],
                'auto_publish' => ['sometimes', 'boolean'],
                'title'        => ['sometimes', 'string', 'max:100'],
                'description'  => ['sometimes', 'string', 'max:5000'],
            ]);

            $channel = $this->updateChannel->execute(new UpdateChannelRequest(
                channelId:   ChannelId::fromString($channelId),
                userId:      new IntegerId((int) Auth::id()),
                autoSync:    array_key_exists('auto_sync', $validated)    ? (bool) $validated['auto_sync']    : null,
                autoPublish: array_key_exists('auto_publish', $validated) ? (bool) $validated['auto_publish'] : null,
                title:       array_key_exists('title', $validated)       ? (string) $validated['title']       : null,
                description: array_key_exists('description', $validated)  ? (string) $validated['description'] : null,
            ));

            return response()->json(['data' => $channel->toArray()], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ChannelNotFound $e) {
            return response()->json([
                'error'   => 'Channel not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to update channel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
