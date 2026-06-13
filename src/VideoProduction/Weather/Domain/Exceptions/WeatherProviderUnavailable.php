<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Weather\Domain\Exceptions;

use RuntimeException;
use Throwable;

final class WeatherProviderUnavailable extends RuntimeException
{
    public static function aemet(?Throwable $previous = null): self
    {
        return new self(
            'El servicio meteorológico (AEMET) no está disponible en este momento. Inténtalo de nuevo en unos minutos.',
            0,
            $previous,
        );
    }
}
