<?php

declare(strict_types=1);

namespace GR\Telephponic\Test\Trace;

use GR\Telephponic\Trace\Builder\Builder;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that span processors receive onStart and onEnd in the right order
 * and with the right data as spans are created and ended via Telephponic.
 */
class SpanProcessorBehaviorTest extends TestCase
{
    private function builder(): Builder
    {
        return Builder::get('test', 'ns', 'test')
            ->withInMemoryExporter()
            ->disableShutDown();
    }

    // -------------------------------------------------------------------------
    // onStart
    // -------------------------------------------------------------------------

    public function test_onStart_is_called_when_span_starts(): void
    {
        $spy = new SpanProcessorSpy();

        $telephponic = $this->builder()->withSpanProcessor($spy)->build();
        // Constructor already started the root span.
        $this->assertCount(1, $spy->starts);

        $telephponic->start('my-span');
        $this->assertCount(2, $spy->starts);
        $this->assertSame('my-span', $spy->starts[1]['name']);
    }

    public function test_onStart_is_called_on_all_processors(): void
    {
        $spy1 = new SpanProcessorSpy();
        $spy2 = new SpanProcessorSpy();

        $telephponic = $this->builder()
            ->withSpanProcessor($spy1)
            ->withSpanProcessor($spy2)
            ->build();

        $telephponic->start('span');

        $this->assertCount(2, $spy1->starts);
        $this->assertCount(2, $spy2->starts);
    }

    // -------------------------------------------------------------------------
    // onEnd
    // -------------------------------------------------------------------------

    public function test_onEnd_is_called_when_span_ends(): void
    {
        $spy = new SpanProcessorSpy();

        $telephponic = $this->builder()->withSpanProcessor($spy)->build();
        $telephponic->start('child');
        $telephponic->end();

        $this->assertCount(1, $spy->ends);
        $this->assertSame('child', $spy->ends[0]['name']);
    }

    public function test_onEnd_is_called_on_all_processors(): void
    {
        $spy1 = new SpanProcessorSpy();
        $spy2 = new SpanProcessorSpy();

        $telephponic = $this->builder()
            ->withSpanProcessor($spy1)
            ->withSpanProcessor($spy2)
            ->build();

        $telephponic->start('span');
        $telephponic->end();

        $this->assertCount(1, $spy1->ends);
        $this->assertCount(1, $spy2->ends);
    }

    // -------------------------------------------------------------------------
    // onStart before onEnd
    // -------------------------------------------------------------------------

    public function test_onStart_is_called_before_onEnd(): void
    {
        $order = [];

        $orderSpy = new class($order) implements \OpenTelemetry\SDK\Trace\SpanProcessorInterface {
            public function __construct(private array &$order) {}

            public function onStart(\OpenTelemetry\SDK\Trace\ReadWriteSpanInterface $span, \OpenTelemetry\Context\ContextInterface $parentContext): void
            {
                $this->order[] = 'start:' . $span->getName();
            }

            public function onEnd(\OpenTelemetry\SDK\Trace\ReadableSpanInterface $span): void
            {
                $this->order[] = 'end:' . $span->getName();
            }

            public function shutdown(?\OpenTelemetry\SDK\Common\Future\CancellationInterface $c = null): bool { return true; }
            public function forceFlush(?\OpenTelemetry\SDK\Common\Future\CancellationInterface $c = null): bool { return true; }
        };

        $telephponic = $this->builder()->withSpanProcessor($orderSpy)->build();
        $telephponic->start('op');
        $telephponic->end();

        $rootName = $_SERVER['REQUEST_URI'] ?? $_SERVER['argv'][0] ?? 'unknown';
        $this->assertSame(['start:' . $rootName, 'start:op', 'end:op'], $order);
    }

    // -------------------------------------------------------------------------
    // endNanos > startNanos
    // -------------------------------------------------------------------------

    public function test_end_epoch_nanos_is_after_start_epoch_nanos(): void
    {
        $spy = new SpanProcessorSpy();

        $telephponic = $this->builder()->withSpanProcessor($spy)->build();
        $telephponic->start('timed');
        $telephponic->end();

        $startNanos = $spy->starts[1]['startNanos'];
        $endNanos   = $spy->ends[0]['endNanos'];

        $this->assertGreaterThan($startNanos, $endNanos);
    }
}
