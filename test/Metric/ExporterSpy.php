<?php

declare(strict_types=1);

namespace GR\Telephponic\Test\Metric;

use GR\Telephponic\Metric\Contract\Exporter;
use GR\Telephponic\Metric\Type\Metric;
use GR\Telephponic\Metric\ValueObject\Type;

class ExporterSpy implements Exporter
{
    public array $metrics = [];
    public int $registerCallCount = 0;
    public int $exportCallCount = 0;

    public function register(Metric ...$metrics): void
    {
        $this->registerCallCount++;
        foreach ($metrics as $metric) {
            $this->metrics[$this->formatKey($metric->type(), $metric->name())] = $metric;
        }
    }

    private function formatKey(Type $type, string $name): string
    {
        return sprintf("%s_%s", $type->value, $name);
    }

    public function export(): void
    {
        $this->exportCallCount++;
    }

    public function hasMetric(Metric $metric): bool
    {
        return $this->hasMetricTypeAndName($metric->type(), $metric->name()) &&
               $this->metrics[$this->formatKey($metric->type(), $metric->name())] === $metric;
    }

    public function hasMetricTypeAndName(Type $type, string $name): bool
    {
        return isset($this->metrics[$this->formatKey($type, $name)]);
    }

    public function callsToRegister(): int
    {
        return $this->registerCallCount;
    }

    public function callsToExport(): int
    {
        return $this->exportCallCount;
    }

    public function countMetrics(): int
    {
        return count($this->metrics);
    }

    public function hasValueForMetricTypeAndName(Type $type, string $name, mixed $value): bool
    {
        return $this->hasMetricTypeAndName($type, $name) &&
               $this->metrics[$this->formatKey($type, $name)]->value() === $value;
    }
}
