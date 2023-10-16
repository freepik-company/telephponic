<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Stacktrace;

class PlainTextStacktraceProvider implements StacktraceProvider
{

    public function getStacktraces(): string
    {
        $stacktraces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        // clean index 0-1
        // 0: PlainTextStacktraceProvider->getStacktraces()
        // 1: Telephponic->end()
        unset($stacktraces[0], $stacktraces[1]);
        $stacktraces = array_values($stacktraces);
        $maxIndex = count($stacktraces) - 1;
        $numOfSpaces = (int)log10($maxIndex) + 1;

        $output = '';

        // if trace is created using hooks or autointrumentation, the first stacktrace is the hook itself, remove it.
        if (str_contains(($stacktraces[0]['function'] ?? ''), '{closure}')) {
            unset($stacktraces[0]);
            $stacktraces = array_values($stacktraces);
        }

        foreach ($stacktraces as $index => $stacktrace) {
            $output .= sprintf(
                "#%0{$numOfSpaces}s %s:%d %s%s%s()\n",
                $index,
                $stacktrace['file'] ?? 'main',
                $stacktrace['line'] ?? 0,
                $stacktrace['class'] ?? '',
                $stacktrace['type'] ?? '',
                $stacktrace['function'] ?? 'unknown'
            );
        }

        return trim($output);
    }
}
