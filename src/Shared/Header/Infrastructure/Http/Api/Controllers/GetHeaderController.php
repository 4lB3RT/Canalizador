<?php

declare(strict_types=1);

namespace Helmreel\Shared\Header\Infrastructure\Http\Api\Controllers;

use Helmreel\Shared\Header\Application\UseCases\GetHeader\GetHeader;
use Helmreel\Shared\Header\Application\UseCases\GetHeader\GetHeaderRequest;
use Helmreel\Shared\Header\Domain\Exceptions\UserHeaderNotFound;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class GetHeaderController extends Controller
{
    public function __construct(
        private readonly GetHeader $getHeader,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $response = $this->getHeader->execute(new GetHeaderRequest(
                userId: new IntegerId((int) Auth::id()),
            ));

            $avatarUrl = $response->avatarPath
                ? Storage::disk('public')->url($response->avatarPath)
                : null;

            return response()->json([
                'data' => [
                    'user' => [
                        'id'         => $response->userId->value(),
                        'name'       => $response->name,
                        'email'      => $response->email,
                        'avatar_url' => $avatarUrl,
                    ],
                    'google_linked'   => $response->googleLinked,
                    'channels_count'  => $response->channelsCount->value(),
                ],
            ], 200);
        } catch (UserHeaderNotFound $e) {
            return response()->json([
                'error'   => 'User not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to fetch header',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
