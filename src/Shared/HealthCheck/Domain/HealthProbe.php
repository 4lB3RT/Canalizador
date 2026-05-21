<?php

declare(strict_types=1);

namespace Canalizador\Shared\HealthCheck\Domain;

interface HealthProbe
{
    public function check(): ServiceStatus;
}
