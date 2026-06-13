<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Domain\Exceptions;

use Exception;

final class VoiceBlocked extends Exception
{
    public static function byPlatform(): self
    {
        return new self(
            'La voz ha sido bloqueada por ElevenLabs por una posible violación de sus términos de servicio. Clona otra voz a partir de un audio del que tengas los derechos.'
        );
    }
}
