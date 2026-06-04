<?php

declare(strict_types=1);

namespace GR\Telephponic\Test\Trace;

use GR\Telephponic\Trace\Builder\Builder;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    // -------------------------------------------------------------------------
    // withSpanProcessor
    // -------------------------------------------------------------------------

    public function test_withSpanProcessor_processor_receives_onStart_for_every_span(): void
    {
        $spy = new SpanProcessorSpy();

        $telephponic = Builder::get('test', 'ns', 'test')
            ->withInMemoryExporter()
            ->disableShutDown()
            ->withSpanProcessor($spy)
            ->build();

        // Constructor creates one root span; one call expected so far.
        $this->assertCount(1, $spy->starts);

        $telephponic->start('child-span');

        $this->assertCount(2, $spy->starts);
        $this->assertSame('child-span', $spy->starts[1]['name']);
    }

    public function test_withSpanProcessor_multiple_processors_all_called(): void
    {
        $spy1 = new SpanProcessorSpy();
        $spy2 = new SpanProcessorSpy();

        Builder::get('test', 'ns', 'test')
            ->withInMemoryExporter()
            ->disableShutDown()
            ->withSpanProcessor($spy1)
            ->withSpanProcessor($spy2)
            ->build();

        $this->assertCount(1, $spy1->starts);
        $this->assertCount(1, $spy2->starts);
    }

    public function test_withDefaultResource_uses_deployment_environment_name_constant(): void
    {
        $this->assertSame('deployment.environment', ResourceAttributes::DEPLOYMENT_ENVIRONMENT);

        // Building also exercises withDefaultResource() internally — any use of the
        // old constant would throw "Undefined constant" here.
        $telephponic = Builder::get('app', 'ns', 'production')
            ->withInMemoryExporter()
            ->disableShutDown()
            ->build();

        $this->assertNotNull($telephponic);
    }
}
