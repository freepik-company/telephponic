<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Exporter;

use GR\Telephponic\Metric\Contract\Exporter;
use GR\Telephponic\Metric\Contract\ExportFormatter;
use GR\Telephponic\Metric\Type\Metric;
use GR\Telephponic\Metric\ValueObject\LogLevel;
use Psr\Log\LoggerInterface;

class LoggerExporter implements Exporter
{

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ExportFormatter $exportFormatter,
        private readonly LogLevel $logLevel = LogLevel::Info,
    ) {
    }

    public function register(Metric ...$metrics): void
    {
        foreach ($metrics as $metric) {
            $this->logger->log(
                $this->logLevel->toPsrLogLevel(),
                $this->exportFormatter->export($metric)
            );
        }
    }

    public function export(): void
    {
        return;
    }
}
