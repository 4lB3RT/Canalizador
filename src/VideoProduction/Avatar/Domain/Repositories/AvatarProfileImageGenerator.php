<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarData;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;

interface AvatarProfileImageGenerator
{
    public function generate(AvatarData $data, Category $category): LocalPath;
}
