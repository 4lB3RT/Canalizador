<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\ValueObjects;

final readonly class AvatarData
{
    public function __construct(
        public AvatarName $name,
        public Biography $biography,
        public PresentationStyle $presentationStyle,
    ) {
    }
}
