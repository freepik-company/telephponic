<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

class Memcached extends AbstractIntegration
{
    protected function getMethods(): array
    {
        return [
            \Memcached::class => [
                'add' => [
                    'type' => 'memcached/add',
                ],
                'addByKey' => [
                    'type' => 'memcached/add',
                ],
                'append' => [
                    'type' => 'memcached/append',
                ],
                'appendByKey' => [
                    'type' => 'memcached/append',
                ],
                'cas' => [
                    'type' => 'memcached/cas',
                ],
                'casByKey' => [
                    'type' => 'memcached/cas',
                ],
                'decrement' => [
                    'type' => 'memcached/decrement',
                ],
                'decrementByKey' => [
                    'type' => 'memcached/decrement',
                ],
                'delete' => [
                    'type' => 'memcached/delete',
                ],
                'deleteByKey' => [
                    'type' => 'memcached/delete',
                ],
                'deleteMulti' => [
                    'type' => 'memcached/delete',
                ],
                'deleteMultiByKey' => [
                    'type' => 'memcached/delete',
                ],
                'fetch' => [
                    'type' => 'memcached/fetch',
                ],
                'fetchAll' => [
                    'type' => 'memcached/fetch',
                ],
                'flush' => [
                    'type' => 'memcached/flush',
                ],
                'get' => [
                    'type' => 'memcached/get',
                ],
                'getByKey' => [
                    'type' => 'memcached/get',
                ],
                'getMulti' => [
                    'type' => 'memcached/get',
                ],
                'getMultiByKey' => [
                    'type' => 'memcached/get',
                ],
                'getAllKeys' => [
                    'type' => 'memcached/get',
                ],
                'getDelayed' => [
                    'type' => 'memcached/get',
                ],
                'getDelayedByKey' => [
                    'type' => 'memcached/get',
                ],
                'getOption' => [
                    'type' => 'memcached/get',
                ],
                'getResultCode' => [
                    'type' => 'memcached/get',
                ],
                'getResultMessage' => [
                    'type' => 'memcached/get',
                ],
                'increment' => [
                    'type' => 'memcached/increment',
                ],
                'incrementByKey' => [
                    'type' => 'memcached/increment',
                ],
                'prepend' => [
                    'type' => 'memcached/prepend',
                ],
                'prependByKey' => [
                    'type' => 'memcached/prepend',
                ],
                'replace' => [
                    'type' => 'memcached/replace',
                ],
                'replaceByKey' => [
                    'type' => 'memcached/replace',
                ],
                'set' => [
                    'type' => 'memcached/set',
                ],
                'setByKey' => [
                    'type' => 'memcached/set',
                ],
                'setMulti' => [
                    'type' => 'memcached/set',
                ],
                'setMultiByKey' => [
                    'type' => 'memcached/set',
                ],
                'setOption' => [
                    'type' => 'memcached/set',
                ],
                'touch' => [
                    'type' => 'memcached/touch',
                ],
                'touchByKey' => [
                    'type' => 'memcached/touch',
                ],
            ],
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}