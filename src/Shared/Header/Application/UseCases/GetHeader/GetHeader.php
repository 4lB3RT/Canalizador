<?php

declare(strict_types=1);

namespace Helmreel\Shared\Header\Application\UseCases\GetHeader;

use Helmreel\Shared\Header\Domain\Exceptions\UserHeaderNotFound;
use Helmreel\Shared\Header\Domain\UserHeaderRepository;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;

final readonly class GetHeader
{
    public function __construct(
        private UserHeaderRepository $userHeaderRepository,
        private ChannelRepository $channelRepository,
    ) {
    }

    /**
     * @throws UserHeaderNotFound
     */
    public function execute(GetHeaderRequest $request): GetHeaderResponse
    {
        $user = $this->userHeaderRepository->findById($request->userId);
        $channelsCount = $this->channelRepository->countByUserId($request->userId);

        return new GetHeaderResponse(
            userId:        $user->id,
            name:          $user->name,
            email:         $user->email,
            googleLinked:  $user->googleLinked,
            channelsCount: $channelsCount,
            avatarPath:    $user->avatarPath,
        );
    }
}
