<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use GR\Telephponic\Trace\Telephponic;
use JsonException;

abstract class AbstractIntegration implements Integration
{
    public function load(Telephponic $tp): void
    {
        foreach ($this->getMethods() as $class => $methods) {
            foreach ($methods as $method => $callable) {
                $tp->addWatcherToMethod($class, $method, $callable);
            }
        }

        foreach ($this->getFunctions() as $function => $callable) {
            $tp->addWatcherToFunction($function, $callable);
        }
    }

    abstract protected function getMethods(): array;

    abstract protected function getFunctions(): array;

    protected function convertToValue(mixed $value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string)$value;
        }

        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value
                ? 'true'
                : 'false';
        }

        if (is_resource($value)) {
            return sprintf("resource %s#%d", get_resource_type($value), (int)$value);
        }

        if (is_array($value) || is_object($value)) {
            try {
                return json_encode($value, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // Do nothing. Will return 'Unknown value'
            }
        }

        return 'Unknown value';
    }

    protected function generateTraceParams(string $type, array $array): array
    {
        return ['_type' => $type] + $array;
    }
}