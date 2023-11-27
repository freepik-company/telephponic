<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Type;

use GR\Telephponic\Metric\ValueObject\Type;
use GR\Telephponic\Metric\ValueObject\Unit;

class Counter implements Metric
{
    public function __construct(
        private string $name,
        protected int|float $value = 0.0,
        private Unit $unit = Unit::Count,
        private ?string $description = null,
    ) {
    }

    public function normalize(): array
    {
        return [
            'type' => $this->type()->value,
            'name' => $this->name,
            'value' => $this->value,
            'unit' => $this->unit->value,
            'description' => $this->description,
        ];
    }

    public function type(): Type
    {
        return Type::Counter;
    }

    public function record(float|int $value): void
    {
        $this->increment($value);
    }

    public function increment(int|float $quantity = 1.0): void
    {
        $this->value += $quantity;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): float|int
    {
        return $this->value;
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
