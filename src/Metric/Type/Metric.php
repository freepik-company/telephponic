<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Type;

use GR\Telephponic\Metric\ValueObject\Type;
use GR\Telephponic\Metric\ValueObject\Unit;

interface Metric
{
    public function type(): Type;

    public function normalize(): array;

    public function record(int|float $value): void;

    public function name(): string;

    public function unit(): Unit;

    public function description(): ?string;
}
