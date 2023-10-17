<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use RuntimeException;

class Memcached extends AbstractIntegration
{
    /** @throws RuntimeException */
    public function __construct(
        private readonly bool $traceAdd = true,
        private readonly bool $traceDelete = true,
        private readonly bool $traceDeleteMulti = true,
        private readonly bool $traceGet = true,
        private readonly bool $traceGetMulti = true,
        private readonly bool $traceSet = true,
        private readonly bool $traceSetMulti = true,
    ) {
        if (!extension_loaded('memcached')) {
            throw new RuntimeException('Memcached extension is not loaded');
        }
    }

    protected function getMethods(): array
    {
        $methods = [
            \Memcached::class => [],
        ];

        if ($this->traceAdd) {
            $methods[\Memcached::class]['add'] = function (\Memcached $object, string $key, mixed $value, int $expiration) {
                return $this->generateTraceParams(
                    'memcached/add',
                    [
                        'memcached.instance' => spl_object_hash($object),
                        'memcached.key' => $this->convertToValue($key),
                        'memcached.value' => $this->convertToValue($value),
                        'memcached.expiration' => $this->convertToValue($expiration),
                    ]
                );
            };
        }

        if ($this->traceDelete) {
            $methods[\Memcached::class]['delete'] = function (\Memcached $object, string $key, int $time = 0) {
                return $this->generateTraceParams(
                    'memcached/delete',
                    [
                        'memcached.instance' => spl_object_hash($object),
                        'memcached.key' => $this->convertToValue($key),
                        'memcached.time' => $this->convertToValue($time),
                    ]
                );
            };
        }

        if ($this->traceDeleteMulti) {
            $methods[\Memcached::class]['deleteMulti'] = function (\Memcached $object, array $keys, int $time = 0) {
                return $this->generateTraceParams(
                    'memcached/delete',
                    [
                        'memcached.instance' => spl_object_hash($object),
                        'memcached.keys' => $this->convertToValue($keys),
                        'memcached.time' => $this->convertToValue($time),
                    ]
                );
            };
        }

        if ($this->traceGet) {
            $methods[\Memcached::class]['get'] = function (\Memcached $object, string $key) {
                return $this->generateTraceParams(
                    'memcached/get',
                    [
                        'memcached.instance' => spl_object_hash($object),
                        'memcached.key' => $this->convertToValue($key),
                    ]
                );
            };
        }

        if ($this->traceGetMulti) {
            $methods[\Memcached::class]['getMulti'] = function (\Memcached $object, array $keys) {
                return $this->generateTraceParams(
                    'memcached/get',
                    [
                        'memcached.instance' => spl_object_hash($object),
                        'memcached.keys' => $this->convertToValue($keys),
                    ]
                );
            };
        }

        if ($this->traceSet) {
            $methods[\Memcached::class]['set'] = function (\Memcached $object, string $key, mixed $value, int $expiration) {
                return $this->generateTraceParams(
                    'memcached/set',
                    [
                        'memcached.instance' => spl_object_hash($object),
                        'memcached.key' => $this->convertToValue($key),
                        'memcached.value' => $this->convertToValue($value),
                        'memcached.expiration' => $this->convertToValue($expiration),
                    ]
                );
            };
        }

        if ($this->traceSetMulti) {
            $methods[\Memcached::class]['setMulti'] = function (\Memcached $object, array $items, int $expiration) {
                return $this->generateTraceParams(
                    'memcached/set',
                    [
                        'memcached.instance' => spl_object_hash($object),
                        'memcached.items' => $this->convertToValue($items),
                        'memcached.expiration' => $this->convertToValue($expiration),
                    ]
                );
            };
        }

        return $methods;
    }

    protected function getFunctions(): array
    {
        return [];
    }
}
