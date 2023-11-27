<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\ValueObject;

enum Unit: string
{
    case Milliseconds = 'ms';
    case Seconds = 's';
    case Bytes = 'B';
    case Items = 'items';
    case Percent = '%';
    case Count = 'count';
}
