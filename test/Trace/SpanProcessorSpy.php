<?php

declare(strict_types=1);

namespace GR\Telephponic\Test\Trace;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

final class SpanProcessorSpy implements SpanProcessorInterface
{
    /** @var array<array{name: string, startNanos: int}> */
    public array $starts = [];

    public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void
    {
        $this->starts[] = [
            'name'       => $span->getName(),
            'startNanos' => $span->toSpanData()->getStartEpochNanos(),
        ];
    }

    public function onEnd(ReadableSpanInterface $span): void
    {
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
}
