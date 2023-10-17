<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Stacktrace;

interface StacktraceProvider
{
    public function getStacktraces(): mixed;
}
