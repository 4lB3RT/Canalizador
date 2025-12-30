<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Entities;

use Canalizador\Script\Domain\ValueObjects\ScriptContent;
use Canalizador\Script\Domain\ValueObjects\ScriptId;

final readonly class Script
{
    public function __construct(
        private ScriptId $id,
        private ScriptContent $content,
    ) {
    }

    public function id(): ScriptId
    {
        return $this->id;
    }

    public function content(): ScriptContent
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'content' => $this->content->value(),
        ];
    }
}
