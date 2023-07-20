<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use GR\Telephponic\Trace\Telephponic;

abstract class AbstractIntegration implements Integration
{
    public function load(Telephponic $tp): void
    {
        foreach ($this->getMethods() as $class => $methods) {
            foreach ($methods as $method => $options) {
                $tp->addWatcherToMethod($class, $method);
            }
        }

        foreach ($this->getFunctions() as $function => $options) {
            $tp->addWatcherToFunction($function);
        }
    }

    abstract protected function getMethods(): array;

    abstract protected function getFunctions(): array;
}