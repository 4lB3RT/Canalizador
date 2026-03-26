<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Metric\Domain\Entities;

use Canalizador\YouTube\Metric\Domain\ValueObjects\MetricName;
use Canalizador\YouTube\Metric\Domain\ValueObjects\MetricType;
use Canalizador\YouTube\Metric\Domain\ValueObjects\MetricValue;

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
