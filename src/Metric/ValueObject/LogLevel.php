<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\ValueObject;

use Psr\Log\LogLevel as PsrLogLevel;

enum LogLevel: string
{
    case Emergency = 'emergency';
    case Alert = 'alert';
    case Critical = 'critical';
    case Error = 'error';
    case Warning = 'warning';
    case Notice = 'notice';
    case Info = 'info';
    case Debug = 'debug';

    public function toPsrLogLevel(): string
    {
        return match ($this) {
            self::Emergency => PsrLogLevel::EMERGENCY,
            self::Alert => PsrLogLevel::ALERT,
            self::Critical => PsrLogLevel::CRITICAL,
            self::Error => PsrLogLevel::ERROR,
            self::Warning => PsrLogLevel::WARNING,
            self::Notice => PsrLogLevel::NOTICE,
            self::Info => PsrLogLevel::INFO,
            self::Debug => PsrLogLevel::DEBUG,
        };
    }
}
