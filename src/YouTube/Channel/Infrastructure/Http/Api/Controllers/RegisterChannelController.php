<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use Helmreel\YouTube\Channel\Application\UseCases\RegisterChannel\RegisterChannel;
use Helmreel\YouTube\Channel\Application\UseCases\RegisterChannel\RegisterChannelRequest;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

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

            $validated = $request->validate([
                'state' => ['required', 'string'],
            ]);

            $row = DB::table('channel_oauth_states')->where('state', $validated['state'])->first();

            if (!$row || (int) $row->user_id !== (int) $user->id) {
                return response()->json([
                    'error'   => 'State not found',
                    'message' => 'Invalid or expired OAuth state.',
                ], 404);
            }

            if (new DateTimeImmutable((string) $row->expires_state_at) < new DateTimeImmutable()) {
                return response()->json([
                    'error'   => 'State expired',
                    'message' => 'OAuth state has expired. Please start the flow again.',
                ], 410);
            }

            if (empty($row->access_token)) {
                return response()->json([
                    'error'   => 'OAuth not completed',
                    'message' => 'Waiting for Google OAuth to complete.',
                ], 409);
            }

            $availableChannelIds = array_map(
                static fn (array $channel) => (string) ($channel['id'] ?? ''),
                json_decode((string) $row->available_channels, true) ?: [],
            );

            if (!in_array($channelId, $availableChannelIds, true)) {
                return response()->json([
                    'message' => 'Selected channel is not in the OAuth response.',
                    'errors'  => [
                        'channel_id' => ['Este canal no está disponible para la cuenta de Google conectada.'],
                    ],
                ], 422);
            }

            $this->registerChannel->execute(new RegisterChannelRequest(
                channelId: $channelId,
                userId:    $user->id,
            ));

            $expiresAt = $row->expires_at
                ? new DateTimeImmutable((string) $row->expires_at)
                : null;

            ChannelDAO::where('id', $channelId)->update([
                'access_token'     => Crypt::decryptString((string) $row->access_token),
                'refresh_token'    => $row->refresh_token ? Crypt::decryptString((string) $row->refresh_token) : null,
                'token_expires_at' => $expiresAt,
                'token_scope'      => $row->scope ? (string) $row->scope : null,
                'token_type'       => $row->token_type ? (string) $row->token_type : null,
            ]);

            DB::table('channel_oauth_states')->where('state', $validated['state'])->delete();

            return response()->json(null, 204);
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
                'error'   => 'Failed to register channel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
