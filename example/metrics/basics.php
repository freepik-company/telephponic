<?php

declare(strict_types=1);

use GR\Telephponic\Metric\Exporter\LoggerExporter;
use GR\Telephponic\Metric\ExportFormatter\JsonExportFormatter;
use GR\Telephponic\Metric\Logger\SimpleStdOutLogger;
use GR\Telephponic\Metric\Meter;
use GR\Telephponic\Metric\ValueObject\Type;

require_once __DIR__ . '/../../vendor/autoload.php';

$meter = new Meter(
    new LoggerExporter(new SimpleStdOutLogger(true), new JsonExportFormatter())
);

$meter->record(Type::Counter, 'counter001', 1);
$meter->record(Type::Counter, 'counter001', 2);
$meter->record(Type::Histogram, 'histogram001', random_int(1, 100));
usleep(random_int(100, 1000));
$meter->record(Type::Histogram, 'histogram001', random_int(1, 100));
usleep(random_int(100, 1000));
$meter->record(Type::Histogram, 'histogram001', random_int(1, 100));
usleep(random_int(100, 1000));
$meter->record(Type::Histogram, 'histogram001', random_int(1, 100));
usleep(random_int(100, 1000));
$meter->record(Type::Histogram, 'histogram001', random_int(1, 100));

$meter->record(Type::Gauge, 'gauge001', 1);
$meter->record(Type::Gauge, 'gauge001', 1);
$meter->record(Type::Gauge, 'gauge001', 1);
$meter->record(Type::Gauge, 'gauge001', 1);
