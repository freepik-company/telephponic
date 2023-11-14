<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Type;

use GR\Telephponic\Metric\ValueObject\Type;

class Gauge extends Counter
{
    public function decrement(int|float $quantity = 1.0): void
    {
        $this->value -= $quantity;
    }

    public function type(): Type
    {
        return Type::Gauge;
    }
}
