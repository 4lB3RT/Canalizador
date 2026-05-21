<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\Entities\ChannelGoogleToken;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelGoogleTokenRepository;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Channel\Infrastructure\Repositories\Youtube\YoutubeChannelRepository;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class LinkChannelController extends Controller
{
    public function __construct(
        private readonly YoutubeChannelRepository    $youtubeChannelRepository,
        private readonly ChannelRepository           $channelRepository,
        private readonly ChannelGoogleTokenRepository $channelGoogleTokenRepository,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'state'      => ['required', 'string'],
                'channel_id' => ['required', 'string', 'min:4', 'max:60'],
            ]);

            $userId = (int) Auth::id();

            $row = DB::table('channel_oauth_states')->where('state', $validated['state'])->first();

            if (!$row || (int) $row->user_id !== $userId) {
                return response()->json([
                    'error'   => 'State not found',
                    'message' => 'Invalid or expired OAuth state.',
                ], 404);
            }

            $expiresStateAt = new DateTimeImmutable((string) $row->expires_state_at);
            if ($expiresStateAt < new DateTimeImmutable()) {
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

            $availableChannels = json_decode((string) $row->available_channels, true) ?: [];
            $ids = array_map(static fn (array $c) => (string) ($c['id'] ?? ''), $availableChannels);
            if (!in_array($validated['channel_id'], $ids, true)) {
                return response()->json([
                    'message' => 'Selected channel is not in the OAuth response.',
                    'errors'  => [
                        'channel_id' => ['Este canal no está disponible para la cuenta de Google conectada.'],
                    ],
                ], 422);
            }

            $channelIdVO = ChannelId::fromString((string) $validated['channel_id']);

            // 1. Cargar metadata desde YouTube (este repositorio usa apiKey, no OAuth token).
            $channel = $this->youtubeChannelRepository->findById($channelIdVO);

            // 2. Asociar al usuario y guardar en BD local.
            $channel->updateUserId(new IntegerId($userId));
            $this->channelRepository->save($channel);

            // 3. Persistir el token Google asociado al canal.
            $expiresAt = $row->expires_at
                ? new DateTimeImmutable((string) $row->expires_at)
                : null;

            $this->channelGoogleTokenRepository->save(new ChannelGoogleToken(
                channelId:    $channelIdVO,
                accessToken:  (string) $row->access_token,
                refreshToken: $row->refresh_token ? (string) $row->refresh_token : null,
                expiresAt:    $expiresAt,
                scope:        $row->scope ? (string) $row->scope : null,
                tokenType:    $row->token_type ? (string) $row->token_type : null,
            ));

            // 4. Limpieza: borrar el state, ya consumido.
            DB::table('channel_oauth_states')->where('state', $validated['state'])->delete();

            return response()->json([
                'data' => $channel->toArray(),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to link channel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
