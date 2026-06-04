<?php

declare(strict_types=1);

namespace GR\Telephponic\Test\Trace;

use GR\Telephponic\Trace\Builder\Builder;
use PHPUnit\Framework\TestCase;

class TelephponicTest extends TestCase
{
    // -------------------------------------------------------------------------
    // start() with startTimestampNanos
    // -------------------------------------------------------------------------

    public function test_start_without_timestamp_uses_current_time(): void
    {
        $spy = new SpanProcessorSpy();
        $before = (int) (microtime(true) * 1e9);

        Builder::get('test', 'ns', 'test')
            ->withInMemoryExporter()
            ->disableShutDown()
            ->withSpanProcessor($spy)
            ->build(); // constructor calls start() for root span

        $after = (int) (microtime(true) * 1e9);

        $rootStart = $spy->starts[0]['startNanos'];
        $this->assertGreaterThanOrEqual($before, $rootStart);
        $this->assertLessThanOrEqual($after, $rootStart);
    }

    public function test_start_with_timestamp_overrides_span_start_time(): void
    {
        $spy = new SpanProcessorSpy();

        $telephponic = Builder::get('test', 'ns', 'test')
            ->withInMemoryExporter()
            ->disableShutDown()
            ->withSpanProcessor($spy)
            ->build();

        $expectedNanos = 1_000_000_000; // 1970-01-01T00:00:01Z
        $telephponic->start('timed-span', [], $expectedNanos);

        $timedStart = $spy->starts[1]['startNanos'];
        $this->assertSame($expectedNanos, $timedStart);
    }

    public function test_start_with_null_timestamp_uses_current_time(): void
    {
        $spy = new SpanProcessorSpy();

        $telephponic = Builder::get('test', 'ns', 'test')
            ->withInMemoryExporter()
            ->disableShutDown()
            ->withSpanProcessor($spy)
            ->build();

        $before = (int) (microtime(true) * 1e9);
        $telephponic->start('span', [], null);
        $after = (int) (microtime(true) * 1e9);

        $spanStart = $spy->starts[1]['startNanos'];
        $this->assertGreaterThanOrEqual($before, $spanStart);
        $this->assertLessThanOrEqual($after, $spanStart);
    }
}
