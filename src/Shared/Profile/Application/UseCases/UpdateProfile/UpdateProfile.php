<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Application\UseCases\UpdateProfile;

use Helmreel\Shared\Profile\Domain\Exceptions\EmailAlreadyTaken;
use Helmreel\Shared\Profile\Domain\Exceptions\InvalidCurrentPassword;
use Helmreel\Shared\Profile\Domain\Exceptions\ProfileNotFound;
use Helmreel\Shared\Profile\Domain\ProfileRepository;
use Helmreel\Shared\Shared\Domain\Services\PasswordHasher;

final readonly class UpdateProfile
{
    public function __construct(
        private ProfileRepository $profileRepository,
        private PasswordHasher $passwordHasher,
    ) {
    }

    /**
     * @throws ProfileNotFound
     * @throws EmailAlreadyTaken
     * @throws InvalidCurrentPassword
     */
    public function execute(UpdateProfileRequest $request): UpdateProfileResponse
    {
        if ($request->email !== null) {
            $exists = $this->profileRepository->emailExistsForOtherUser(
                $request->email,
                $request->userId,
            );

            if ($exists) {
                throw EmailAlreadyTaken::withEmail($request->email);
            }
        }

        $hashedPassword = null;
        if ($request->plainPassword !== null) {
            if ($request->currentPassword === null) {
                throw InvalidCurrentPassword::create();
            }

            $currentHash = $this->profileRepository->passwordHashOf($request->userId);
            if (!$this->passwordHasher->check($request->currentPassword, $currentHash)) {
                throw InvalidCurrentPassword::create();
            }

            $hashedPassword = $this->passwordHasher->hash($request->plainPassword);
        }

        $profile = $this->profileRepository->update(
            userId:         $request->userId,
            name:           $request->name,
            email:          $request->email,
            hashedPassword: $hashedPassword,
            avatarPath:     $request->avatarPath,
        );

        return new UpdateProfileResponse(profile: $profile);
    }
}
