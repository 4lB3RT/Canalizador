<?php

declare(strict_types=1);

namespace Canalizador\Voice\Domain\Repositories;

interface AudioIsolator
{
    /**
     * Aísla las vocals/voz del audio, eliminando el fondo.
     *
     * @return string Ruta al audio aislado
     */
    public function isolate(string $audioPath): string;
}
