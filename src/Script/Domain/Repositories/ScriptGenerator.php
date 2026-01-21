<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Repositories;

use Canalizador\Channel\Domain\Entities\Channel;

interface ScriptGenerator
{
    public function generate(?string $prompt = null, Channel $channel): string;
    
    public function generateGaming(?string $prompt = null, ?Channel $channel = null): string;
    
    public function generateAstrology(?string $prompt = null, ?Channel $channel = null): string;
}
