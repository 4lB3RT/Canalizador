<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Metric\Domain\Entities;

use Helmreel\YouTube\Metric\Domain\ValueObjects\MetricName;
use Helmreel\YouTube\Metric\Domain\ValueObjects\MetricType;
use Helmreel\YouTube\Metric\Domain\ValueObjects\MetricValue;

final readonly class Metric
{
    public function __construct(
        private MetricName  $name,
        private MetricType  $type,
        private MetricValue $value
    )
    {
    }

    public function name(): MetricName
    {
        return $this->name;
    }

    public function type(): MetricType
    {
        return $this->type;
    }

    public function value(): MetricValue
    {
        return $this->value;
    }
}
