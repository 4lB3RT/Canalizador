<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\ValueObjects;

final readonly class PublishOptions
{
    /**
     * @param array<string> $tags
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public array $tags = [],
        public string $privacyStatus = 'private',
    ) {
        if (!in_array($privacyStatus, ['private', 'unlisted', 'public'], true)) {
            throw new \InvalidArgumentException("Invalid privacy status: {$privacyStatus}");
        }
    }
}
