<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

class Redis extends AbstractIntegration
{

    protected function getMethods(): array
    {
        return [
            \Redis::class => [
                'connect' => [
                    'type' => 'redis/connect',
                ],
                'open' => [
                    'type' => 'redis/connect',
                ],
                'pconnect' => [
                    'type' => 'redis/connect',
                ],
                'popen' => [
                    'type' => 'redis/connect',
                ],
                'close' => [
                    'type' => 'redis/disconnect',
                ],
                'ping' => [
                    'type' => 'redis/ping',
                ],
                'echo' => [
                    'type' => 'redis/ping',
                ],
                'get' => [
                    'type' => 'redis/get',
                ],
                'set' => [
                    'type' => 'redis/set',
                ],
                'setex' => [
                    'type' => 'redis/set',
                ],
                'psetex' => [
                    'type' => 'redis/set',
                ],
                'setnx' => [
                    'type' => 'redis/set',
                ],
                'del' => [
                    'type' => 'redis/del',
                ],
                'delete' => [
                    'type' => 'redis/del',
                ],
                'unlink' => [
                    'type' => 'redis/del',
                ],
                'exists' => [
                    'type' => 'redis/get',
                ],
            ],
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}