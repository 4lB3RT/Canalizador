<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\Repositories;

use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarData;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;

interface AvatarDataGenerator
{
    public function generate(Category $category): AvatarData;
}
