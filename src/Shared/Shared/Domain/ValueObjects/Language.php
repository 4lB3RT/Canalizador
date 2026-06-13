<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\ValueObjects;

enum Language: string
{
    case SPANISH = 'es';
    case ENGLISH = 'en';
    case FRENCH = 'fr';
    case GERMAN = 'de';
    case ITALIAN = 'it';
    case PORTUGUESE = 'pt';

    public function promptLabel(): string
    {
        return match ($this) {
            self::SPANISH => 'Spanish (Spain, European Spanish — not Latin American)',
            self::ENGLISH => 'English',
            self::FRENCH => 'French',
            self::GERMAN => 'German',
            self::ITALIAN => 'Italian',
            self::PORTUGUESE => 'Portuguese',
        };
    }
}
