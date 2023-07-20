<?php

declare(strict_types=1);

namespace Muriano\Telephponic\Trace\Integration;

use Muriano\Telephponic\Trace\Telephponic;

interface Integration
{
    public function load(Telephponic $tp): void;
}