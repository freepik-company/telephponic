<?php

declare(strict_types=1);

namespace GR\Telephponic\Test\Metric;

use GR\Telephponic\Metric\Meter;
use GR\Telephponic\Metric\ValueObject\Type;
use GR\Telephponic\Metric\ValueObject\Unit;
use PHPUnit\Framework\TestCase;

class MeterTest extends TestCase
{

    public function testRecord(): void
    {
        $exporterSpy = $this->exporter();
        $sut = new Meter($exporterSpy);

        $sut->record(Type::Counter, 'counter', 1.0, Unit::Count, 'description');
        $sut->record(Type::Gauge, 'gauge', 1.0, Unit::Count, 'description');
        $sut->record(Type::Histogram, 'histogram', 1.0, Unit::Count, 'description');

        $sut->record(Type::Counter, 'counter', 1.0);
        $sut->record(Type::Counter, 'counter', 1.0);

        $sut->record(Type::Gauge, 'gauge', 1.0);

        $this->assertEquals(3, $exporterSpy->countMetrics());

        $this->assertTrue($exporterSpy->hasMetricTypeAndName(Type::Counter, 'counter'));
        $this->assertTrue($exporterSpy->hasMetricTypeAndName(Type::Gauge, 'gauge'));
        $this->assertTrue($exporterSpy->hasMetricTypeAndName(Type::Histogram, 'histogram'));
    }

    private function exporter(): ExporterSpy
    {
        return new ExporterSpy();
    }

    public function testShutdown(): void
    {
        $exporterSpy = $this->exporter();
        $sut = new Meter($exporterSpy);

        $sut->record(Type::Counter, 'counter', 1.0, Unit::Count, 'description');
        $sut->record(Type::Gauge, 'gauge', 1.0, Unit::Count, 'description');
        $sut->record(Type::Histogram, 'histogram', 1.0, Unit::Count, 'description');

        $sut->record(Type::Counter, 'counter', 1.0);
        $sut->record(Type::Counter, 'counter', 1.0);

        $sut->record(Type::Gauge, 'gauge', 1.0);

        $sut->shutdown();

        $this->assertEquals(1, $exporterSpy->callsToExport());
    }
}
