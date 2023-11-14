<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric;

use GR\Telephponic\Metric\Contract\Exporter;
use GR\Telephponic\Metric\Type\Counter;
use GR\Telephponic\Metric\Type\Gauge;
use GR\Telephponic\Metric\Type\Histogram;
use GR\Telephponic\Metric\Type\Metric;
use GR\Telephponic\Metric\ValueObject\Type;
use GR\Telephponic\Metric\ValueObject\Unit;

class Meter
{
    private array $metrics = [];

    public function __construct(private Exporter $exporter)
    {
    }

    public function record(
        Type $type,
        string $name,
        int|float $value = 0.0,
        Unit $unit = Unit::Count,
        ?string $description = null
    ): void {
        $metric = $this->getMetric($type, $name) ?? $this->createMetric($type, $name, $value, $unit, $description);
        $metric->record($value);
        $this->add($metric);
    }

    public function getMetric(
        Type $type,
        string $name,
    ): ?Metric {
        $key = $this->formatKey($type->value, $name);

        return $this->metrics[$key] ?? null;
    }

    private function formatKey(string $type, string $name): string
    {
        return sprintf("%s_%s", $type, $name);
    }

    public function createMetric(
        Type $type,
        string $name,
        null|float|int $value = null,
        Unit $unit = Unit::Count,
        ?string $description = null
    ): Counter|Gauge|Histogram {
        return match ($type) {
            Type::Counter => $this->createCounter($name, $value, $unit, $description),
            Type::Gauge => $this->createGauge($name, $value, $unit, $description),
            Type::Histogram => $this->createHistogram($name, $unit, $description),
        };
    }

    public function createCounter(string $name, int|float $value = 0.0, Unit $unit = Unit::Count, ?string $description = null): Counter
    {
        return new Counter(
            $name,
            $value,
            Unit::Count,
            $description
        );
    }

    public function createGauge(string $name, int|float $value = 0.0, Unit $unit = Unit::Count, ?string $description = null): Gauge
    {
        return new Gauge(
            $name,
            $value,
            Unit::Count,
            $description
        );
    }

    public function createHistogram(string $name, Unit $unit = Unit::Count, ?string $description = null): Histogram
    {
        return new Histogram(
            $name,
            $unit,
            $description
        );
    }

    public function add(Metric ...$metrics): void
    {
        foreach ($metrics as $metric) {
            $this->metrics[$this->formatKey($metric->type()->value, $metric->name())] = $metric;
        }
        $this->exporter->register(...$metrics);
    }

    public function shutdown(): void
    {
        $this->export();
    }

    public function export(): void
    {
        $this->exporter->export();
    }
}
