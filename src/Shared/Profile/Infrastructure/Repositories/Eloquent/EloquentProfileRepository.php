<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Infrastructure\Repositories\Eloquent;

use App\Models\User;
use Helmreel\Shared\Profile\Domain\Exceptions\ProfileNotFound;
use Helmreel\Shared\Profile\Domain\ProfileData;
use Helmreel\Shared\Profile\Domain\ProfileRepository;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

final class EloquentProfileRepository implements ProfileRepository
{
    public function findById(IntegerId $userId): ProfileData
    {
        $user = User::find($userId->value());

        if (!$user) {
            throw ProfileNotFound::withId($userId->value());
        }

        return $this->toData($user);
    }

    public function passwordHashOf(IntegerId $userId): string
    {
        $user = User::find($userId->value());

        if (!$user) {
            throw ProfileNotFound::withId($userId->value());
        }

        return (string) $user->getAttributes()['password'];
    }

    public function emailExistsForOtherUser(string $email, IntegerId $userId): bool
    {
        return User::where('email', $email)
            ->where('id', '!=', $userId->value())
            ->exists();
    }

    public function update(
        IntegerId $userId,
        ?string $name,
        ?string $email,
        ?string $hashedPassword,
        ?string $avatarPath,
    ): ProfileData {
        $user = User::find($userId->value());

        if (!$user) {
            throw ProfileNotFound::withId($userId->value());
        }

        $attributes = [];

        if ($name !== null) {
            $attributes['name'] = $name;
        }
        if ($email !== null) {
            $attributes['email'] = $email;
        }
        if ($hashedPassword !== null) {
            // The User model casts `password` as `hashed`, so writing a plain value would
            // be hashed again. Bypass the cast by setting the raw attribute.
            $user->setRawAttributes(array_merge($user->getAttributes(), [
                'password' => $hashedPassword,
            ]));
        }
        if ($avatarPath !== null) {
            $attributes['avatar_path'] = $avatarPath;
        }

        if (!empty($attributes)) {
            $user->fill($attributes);
        }

        $user->save();

        return $this->toData($user->fresh());
    }

    private function toData(User $user): ProfileData
    {
        return new ProfileData(
            id:          new IntegerId((int) $user->id),
            name:        (string) $user->name,
            email:       (string) $user->email,
            avatarPath:  $user->avatar_path ? (string) $user->avatar_path : null,
        );
    }
}
