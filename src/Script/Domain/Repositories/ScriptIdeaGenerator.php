<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Repositories;

use Canalizador\Channel\Domain\Entities\Channel;

interface ScriptIdeaGenerator
{
    public function generateIdea(Channel $channel): string;
    
    public function generateGaming(Channel $channel): string;
    
    public function generateAstrology(Channel $channel): string;
}
