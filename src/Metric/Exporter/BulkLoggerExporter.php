<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Exporter;

use GR\Telephponic\Metric\Contract\Exporter;
use GR\Telephponic\Metric\Contract\ExportFormatter;
use GR\Telephponic\Metric\Type\LogLevel;
use GR\Telephponic\Metric\Type\Metric;
use Psr\Log\LoggerInterface;

class BulkLoggerExporter implements Exporter
{

    private array $metrics;

    public function __construct(
        private LoggerInterface $logger,
        private ExportFormatter $exportFormatter,
        private LogLevel $logLevel = LogLevel::Info,
    ) {
        $this->metrics = [];
    }

    public function register(Metric ...$metrics): void
    {
        foreach ($metrics as $metric) {
            $this->metrics[$this->formatKey($metric)] = $metric;
        }
    }

    private function formatKey(Metric $metric): string
    {
        return sprintf("%s_%s", $metric->type()->value, $metric->name());
    }

    public function export(): void
    {
        foreach ($this->metrics as $metric) {
            $this->logger->log(
                $this->logLevel->toPsrLogLevel(),
                $this->exportFormatter->export($metric)
            );
        }
    }
}
