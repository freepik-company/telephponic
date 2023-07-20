<?php

declare(strict_types=1);

namespace Muriano\Telephponic\Trace\Integration;

use Grpc\BaseStub;

class Grpc extends AbstractIntegration
{

    protected function getMethods(): array
    {
        return [
            BaseStub::class => [
                '_simpleRequest' => [
                    'type' => 'grpc/request',
                ],
                '_clientStreamRequest' => [
                    'type' => 'grpc/request',
                ],
                '_serverStreamRequest' => [
                    'type' => 'grpc/request',
                ],
                '_bidiRequest' => [
                    'type' => 'grpc/request',
                ],
            ],
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}