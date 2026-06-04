<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use DateTimeImmutable;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class GoogleChannelOAuthController extends Controller
{
    private const STATE_TTL_MINUTES = 30;

    private const SCOPES = [
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/youtube',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

    /**
     * API endpoint (Bearer-authenticated): genera state + URL para que el front abra una popup.
     */
    public function startApi(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $state = Str::random(48);

        DB::table('channel_oauth_states')->insert([
            'state'              => $state,
            'user_id'            => $user->id,
            'access_token'       => '',
            'refresh_token'      => null,
            'expires_at'         => null,
            'scope'              => null,
            'token_type'         => null,
            'available_channels' => json_encode([]),
            'expires_state_at'   => (new DateTimeImmutable('+' . self::STATE_TTL_MINUTES . ' minutes'))->format('Y-m-d H:i:s'),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $client = $this->buildGoogleClient();
        $client->setState($state);
        $client->setApprovalPrompt('force');
        $client->setPrompt('consent select_account');

        return response()->json([
            'data' => [
                'state' => $state,
                'url'   => $client->createAuthUrl(),
            ],
        ], 200);
    }

    public function callback(Request $request): Response
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $state = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');

        if ($state === '' || $code === '') {
            return $this->renderClose(false, 'Missing state or code.');
        }

        $stateRow = DB::table('channel_oauth_states')->where('state', $state)->first();

        if (!$stateRow) {
            return $this->renderClose(false, 'Invalid state.');
        }

        try {
            $client = $this->buildGoogleClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::warning('Google OAuth channel exchange error', ['error' => $token]);
                return $this->renderClose(false, $token['error_description'] ?? 'OAuth error');
            }

            $client->setAccessToken($token);

            $youtube = new Google_Service_YouTube($client);
            $response = $youtube->channels->listChannels('id,snippet', ['mine' => true]);

            $available = [];
            foreach ($response->getItems() as $item) {
                $snippet = $item->getSnippet();
                $available[] = [
                    'id'            => $item->getId(),
                    'title'         => $snippet?->getTitle() ?? '',
                    'custom_url'    => $snippet?->getCustomUrl(),
                    'thumbnail_url' => $snippet?->getThumbnails()?->getDefault()?->getUrl(),
                ];
            }

            $expiresAt = isset($token['expires_in'])
                ? (new DateTimeImmutable('+' . (int) $token['expires_in'] . ' seconds'))->format('Y-m-d H:i:s')
                : null;

            $accessToken = (string) ($token['access_token'] ?? '');
            $refreshToken = $token['refresh_token'] ?? null;

            DB::table('channel_oauth_states')->where('state', $state)->update([
                'access_token'       => $accessToken !== '' ? Crypt::encryptString($accessToken) : '',
                'refresh_token'      => $refreshToken !== null ? Crypt::encryptString($refreshToken) : null,
                'expires_at'         => $expiresAt,
                'scope'              => $token['scope']                   ?? null,
                'token_type'         => $token['token_type']              ?? null,
                'available_channels' => json_encode($available),
                'updated_at'         => now(),
            ]);

            return $this->renderClose(true, null, $state, $frontendUrl);
        } catch (Throwable $e) {
            Log::error('Google OAuth channel callback failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->renderClose(false, $e->getMessage());
        }
    }

    private function renderClose(bool $success, ?string $error = null, ?string $state = null, ?string $frontendOrigin = null): Response
    {
        $payload = json_encode([
            'type'    => 'helmreel:channel-oauth',
            'success' => $success,
            'state'   => $state,
            'error'   => $error,
        ]);

        $origin = $frontendOrigin
            ?? rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');

        $html = <<<HTML
<!doctype html>
<html lang="es">
  <head><meta charset="utf-8"><title>Helmreel OAuth</title></head>
  <body>
    <script>
      (function () {
        var payload = {$payload};
        try {
          if (window.opener) {
            window.opener.postMessage(payload, "{$origin}");
          }
        } catch (e) {}
        window.close();
      })();
    </script>
    <p>Puedes cerrar esta ventana.</p>
  </body>
</html>
HTML;

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function buildGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(config('services.youtube_analytics.client_id'));
        $client->setClientSecret(config('services.youtube_analytics.client_secret'));
        $client->setRedirectUri(
            url('/auth/google/channel/callback'),
        );
        $client->setScopes(self::SCOPES);
        $client->setAccessType('offline');
        return $client;
    }
}
