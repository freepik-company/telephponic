<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Type;

use GR\Telephponic\Metric\ValueObject\Type;
use GR\Telephponic\Metric\ValueObject\Unit;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;

class Histogram implements Metric
{
    private array $values = [];
    private ClockInterface $clock;

    public function __construct(
        private string $name,
        private Unit $unit,
        private ?string $description = null,
    ) {
        $this->clock = (new ClockFactory())->build();
    }

    public function record(int|float $value): void
    {
        $this->values[$this->clock->now()] = $value;
    }

    public function normalize(): array
    {
        return [
            'type' => $this->type()->value,
            'name' => $this->name,
            'values' => $this->values,
            'unit' => $this->unit->value,
            'description' => $this->description,
        ];
    }

    public function type(): Type
    {
        return Type::Histogram;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function unit(): Unit
    {
        return $this->unit;
    }

    public function description(): ?string
    {
        return $this->description;
    }
}
