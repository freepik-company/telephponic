<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Contract;

use GR\Telephponic\Metric\Type\Metric;

interface Exporter
{
    public function register(Metric ...$metrics): void;

    public function export(): void;
}
