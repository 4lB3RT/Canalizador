<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers;

use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class GetPendingOAuthStateController extends Controller
{
    public function __invoke(Request $request, string $state): JsonResponse
    {
        $row = DB::table('channel_oauth_states')->where('state', $state)->first();

        if (!$row || (int) $row->user_id !== (int) Auth::id()) {
            return response()->json([
                'error'   => 'State not found',
                'message' => 'Invalid or expired OAuth state.',
            ], 404);
        }

        $expiresAt = new DateTimeImmutable((string) $row->expires_state_at);
        if ($expiresAt < new DateTimeImmutable()) {
            return response()->json([
                'error'   => 'State expired',
                'message' => 'OAuth state has expired. Please start the flow again.',
            ], 410);
        }

        if (empty($row->access_token)) {
            return response()->json([
                'data' => [
                    'state'    => $row->state,
                    'pending'  => true,
                    'channels' => [],
                ],
            ], 200);
        }

        $channels = json_decode((string) $row->available_channels, true) ?: [];

        return response()->json([
            'data' => [
                'state'    => $row->state,
                'pending'  => false,
                'channels' => $channels,
            ],
        ], 200);
    }
}
