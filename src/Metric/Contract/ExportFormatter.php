<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Contract;

use GR\Telephponic\Metric\Type\Metric;

interface ExportFormatter
{
    public function export(Metric $metric): string;
}
