<?php

declare(strict_types=1);

namespace Canalizador\Shared\Profile\Infrastructure\Http\Api\Controllers;

use Canalizador\Shared\Profile\Application\UseCases\UpdateProfile\UpdateProfile;
use Canalizador\Shared\Profile\Application\UseCases\UpdateProfile\UpdateProfileRequest;
use Canalizador\Shared\Profile\Domain\Exceptions\EmailAlreadyTaken;
use Canalizador\Shared\Profile\Domain\Exceptions\InvalidCurrentPassword;
use Canalizador\Shared\Profile\Domain\Exceptions\ProfileNotFound;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

final class UpdateProfileController extends Controller
{
    private const AVATAR_DIR = 'avatars';

    public function __construct(
        private readonly UpdateProfile $updateProfile,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'                  => ['sometimes', 'string', 'min:1', 'max:120'],
                'email'                 => ['sometimes', 'email', 'max:200'],
                'password'              => ['sometimes', 'string', 'min:8', 'max:200', 'confirmed'],
                'current_password'      => ['required_with:password', 'string'],
                'avatar'                => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            $userId = new IntegerId((int) Auth::id());

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store(
                    self::AVATAR_DIR . '/' . $userId->value(),
                    'public',
                );

                $previous = Auth::user()?->avatar_path;
                if ($previous && $previous !== $avatarPath) {
                    Storage::disk('public')->delete($previous);
                }
            }

            $response = $this->updateProfile->execute(new UpdateProfileRequest(
                userId:          $userId,
                name:            $validated['name']             ?? null,
                email:           $validated['email']            ?? null,
                plainPassword:   $validated['password']         ?? null,
                currentPassword: $validated['current_password'] ?? null,
                avatarPath:      $avatarPath,
            ));

            $profile = $response->profile;
            $avatarUrl = $profile->avatarPath
                ? Storage::disk('public')->url($profile->avatarPath)
                : null;

            return response()->json([
                'data' => [
                    'id'         => $profile->id->value(),
                    'name'       => $profile->name,
                    'email'      => $profile->email,
                    'avatar_url' => $avatarUrl,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (EmailAlreadyTaken $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => [
                    'email' => ['Este email ya está en uso.'],
                ],
            ], 422);
        } catch (InvalidCurrentPassword $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => [
                    'current_password' => ['La contraseña actual no es correcta.'],
                ],
            ], 422);
        } catch (ProfileNotFound $e) {
            return response()->json([
                'error'   => 'User not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Failed to update profile',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
