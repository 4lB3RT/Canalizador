<?php

declare(strict_types=1);

namespace Canalizador\Shared\Header\Infrastructure\Repositories\Eloquent;

use App\Models\User;
use Canalizador\Shared\Header\Domain\Exceptions\UserHeaderNotFound;
use Canalizador\Shared\Header\Domain\UserHeaderData;
use Canalizador\Shared\Header\Domain\UserHeaderRepository;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

final class EloquentUserHeaderRepository implements UserHeaderRepository
{
    public function findById(IntegerId $userId): UserHeaderData
    {
        $user = User::find($userId->value());

        if (!$user) {
            throw UserHeaderNotFound::withId($userId->value());
        }

        return new UserHeaderData(
            id:           new IntegerId((int) $user->id),
            name:         (string) $user->name,
            email:        (string) $user->email,
            googleLinked: !empty($user->google_access_token),
            avatarPath:   $user->avatar_path ? (string) $user->avatar_path : null,
        );
    }
}
