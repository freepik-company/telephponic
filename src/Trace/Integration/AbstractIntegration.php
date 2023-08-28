<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use GR\Telephponic\Trace\Telephponic;

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
}