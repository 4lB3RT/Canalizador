<?php

declare(strict_types=1);

namespace Helmreel\Shared\HealthCheck\Domain;

interface HealthProbe
{
    public function check(): ServiceStatus;
}
