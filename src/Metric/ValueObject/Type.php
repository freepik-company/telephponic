<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\ValueObject;

enum Type: string
{
    case Counter = 'counter';
    case Gauge = 'gauge';
    case Histogram = 'histogram';
}
