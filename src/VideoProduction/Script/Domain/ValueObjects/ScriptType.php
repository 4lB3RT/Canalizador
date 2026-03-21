<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Script\Domain\ValueObjects;

enum ScriptType: string
{
    case SHORT = 'short';
    case LONG = 'long';
    case EDUCATIONAL = 'educational';
    case PROMOTIONAL = 'promotional';
    case SUMMARY = 'summary';
    case HOOK = 'hook';
}
