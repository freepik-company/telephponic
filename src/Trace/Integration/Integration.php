<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use GR\Telephponic\Trace\Telephponic;

interface Integration
{
    public function load(Telephponic $tp): void;
}