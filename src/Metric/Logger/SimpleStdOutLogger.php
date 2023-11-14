<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\Logger;

use GR\Telephponic\Metric\ValueObject\LogLevel;
use Psr\Log\LoggerInterface;
use Stringable;

class SimpleStdOutLogger implements LoggerInterface
{

    /** @var false|resource */
    private $fileHandler;

    public function __construct(bool $useStdErr = false)
    {
        $this->fileHandler = $useStdErr
            ? fopen('php://stderr', 'wb')
            : fopen('php://stdout', 'wb');
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Emergency->toPsrLogLevel(), $message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        fwrite($this->fileHandler, sprintf('{"%s": %s}%s', $level, $message, PHP_EOL));
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Alert->toPsrLogLevel(), $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Critical->toPsrLogLevel(), $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Error->toPsrLogLevel(), $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning->toPsrLogLevel(), $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Notice->toPsrLogLevel(), $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Info->toPsrLogLevel(), $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::Debug->toPsrLogLevel(), $message, $context);
    }
}
