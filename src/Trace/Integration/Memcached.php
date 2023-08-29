<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

class Memcached extends AbstractIntegration
{
    protected function getMethods(): array
    {
        return [
            \Memcached::class => [
                'add' => function (\Memcached $object, string $key, mixed $value, int $expiration) {
                    return $this->generateTraceParams(
                        'memcached/add',
                        [
                            'memcached.instance' => spl_object_hash($object),
                            'memcached.key' => $this->convertToValue($key),
                            'memcached.value' => $this->convertToValue($value),
                            'memcached.expiration' => $this->convertToValue($expiration),
                        ]
                    );
                },
                'delete' => function (\Memcached $object, string $key, int $time = 0) {
                    return $this->generateTraceParams(
                        'memcached/delete',
                        [
                            'memcached.instance' => spl_object_hash($object),
                            'memcached.key' => $this->convertToValue($key),
                            'memcached.time' => $this->convertToValue($time),
                        ]
                    );
                },
                'deleteMulti' => function (\Memcached $object, array $keys, int $time = 0) {
                    return $this->generateTraceParams(
                        'memcached/delete',
                        [
                            'memcached.instance' => spl_object_hash($object),
                            'memcached.keys' => $this->convertToValue($keys),
                            'memcached.time' => $this->convertToValue($time),
                        ]
                    );
                },
                'get' => function (\Memcached $object, string $key) {
                    return $this->generateTraceParams(
                        'memcached/get',
                        [
                            'memcached.instance' => spl_object_hash($object),
                            'memcached.key' => $this->convertToValue($key),
                        ]
                    );
                },
                'getMulti' => function (\Memcached $object, array $keys) {
                    return $this->generateTraceParams(
                        'memcached/get',
                        [
                            'memcached.instance' => spl_object_hash($object),
                            'memcached.keys' => $this->convertToValue($keys),
                        ]
                    );
                },
                'set' => function (\Memcached $object, string $key, mixed $value, int $expiration) {
                    return $this->generateTraceParams(
                        'memcached/set',
                        [
                            'memcached.instance' => spl_object_hash($object),
                            'memcached.key' => $this->convertToValue($key),
                            'memcached.value' => $this->convertToValue($value),
                            'memcached.expiration' => $this->convertToValue($expiration),
                        ]
                    );
                },
                'setMulti' => function (\Memcached $object, array $items, int $expiration) {
                    return $this->generateTraceParams(
                        'memcached/set',
                        [
                            'memcached.instance' => spl_object_hash($object),
                            'memcached.items' => $this->convertToValue($items),
                            'memcached.expiration' => $this->convertToValue($expiration),
                        ]
                    );
                },
            ],
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}