<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Video\Application\UseCases\SyncVideo\SyncVideo;
use Helmreel\YouTube\Video\Application\UseCases\SyncVideo\SyncVideoRequest;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\ValueObjects\PlatformId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SyncVideoController extends Controller
{
    public function __construct(
        private readonly SyncVideo $syncVideo,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'platform_id' => ['required', 'string', 'min:5', 'max:50'],
            ]);

            $this->syncVideo->execute(new SyncVideoRequest(
                platformId: PlatformId::fromString((string) $validated['platform_id']),
                userId:     new IntegerId((int) Auth::id()),
            ));

            return response()->json([
                'data' => [
                    'platform_id' => $validated['platform_id'],
                    'status'      => 'synced',
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (VideoNotFound | ChannelNotFound $e) {
            return response()->json([
                'error'   => 'Video not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to sync video',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
