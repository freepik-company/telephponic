<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use RuntimeException;

class Redis extends AbstractIntegration
{
    /** @throws RuntimeException */
    public function __construct(
        private readonly bool $traceConnect = true,
        private readonly bool $traceOpen = true,
        private readonly bool $tracePconnect = true,
        private readonly bool $tracePopen = true,
        private readonly bool $traceClose = true,
        private readonly bool $tracePing = true,
        private readonly bool $traceEcho = true,
        private readonly bool $traceGet = true,
        private readonly bool $traceSet = true,
        private readonly bool $traceDel = true,
        private readonly bool $traceDelete = true,
        private readonly bool $traceUnlink = true,
        private readonly bool $traceExists = true,
    ) {
        if (!extension_loaded('redis')) {
            throw new RuntimeException('Redis extension is not loaded');
        }
    }

    public function traceConnect(
        \Redis $redis,
        string $host,
        int $port = 6379,
        float $timeout = 0.0,
        ?string $reserved = null,
        int $retryInterval = 0,
        float $readTimeout = 0.0,
        ?array $context = null
    ): array {
        return $this->generateTraceParams(
            'redis/connect',
            [
                'redis.instance' => spl_object_hash($redis),
                'redis.host' => $host,
                'redis.port' => $port,
                'redis.timeout' => $timeout,
                'redis.reserved' => $reserved,
                'redis.retryInterval' => $retryInterval,
                'redis.readTimeout' => $readTimeout,
                'redis.context' => $context,
            ]
        );
    }

    public function traceClose(\Redis $redis): array
    {
        return $this->generateTraceParams(
            'redis/disconnect',
            [
                'redis.instance' => spl_object_hash($redis),
            ]
        );
    }

    public function tracePing(\Redis $redis, ?string $message = 'ping'): array
    {
        return $this->generateTraceParams(
            'redis/ping',
            [
                'redis.instance' => spl_object_hash($redis),
                'redis.message' => $this->convertToValue($message),
            ]
        );
    }

    public function traceGet(\Redis $redis, string $key): array
    {
        return $this->generateTraceParams(
            'redis/get',
            [
                'redis.instance' => spl_object_hash($redis),
                'redis.key' => $key,
            ]
        );
    }

    public function traceSet(\Redis $redis, string $key, string $value, mixed $options): array
    {
        $params = [
            'redis.instance' => spl_object_hash($redis),
            'redis.key' => $key,
            'redis.value' => $value,
        ];

        if (is_array($options)) {
            $params['redis.options'] = $this->convertToValue($options);
        } else {
            $params['redis.expiration'] = $this->convertToValue($options);
        }

        return $this->generateTraceParams(
            'redis/set',
            $params
        );
    }

    public function traceDel(\Redis $redis, string ...$keys): array
    {
        return $this->generateTraceParams(
            'redis/del',
            [
                'redis.instance' => spl_object_hash($redis),
                'redis.keys' => $this->convertToValue($keys),
            ]
        );
    }

    protected function getMethods(): array
    {
        $methods = [
            \Redis::class => [],
        ];

        if ($this->traceConnect) {
            $methods[\Redis::class]['connect'] = $this->traceConnect(...);
        }

        if ($this->traceOpen) {
            $methods[\Redis::class]['open'] = $this->traceConnect(...);
        }

        if ($this->tracePconnect) {
            $methods[\Redis::class]['pconnect'] = $this->traceConnect(...);
        }

        if ($this->tracePopen) {
            $methods[\Redis::class]['popen'] = $this->traceConnect(...);
        }

        if ($this->traceClose) {
            $methods[\Redis::class]['close'] = $this->traceClose(...);
        }

        if ($this->tracePing) {
            $methods[\Redis::class]['ping'] = $this->tracePing(...);
        }

        if ($this->traceEcho) {
            $methods[\Redis::class]['echo'] = $this->tracePing(...);
        }

        if ($this->traceGet) {
            $methods[\Redis::class]['get'] = $this->traceGet(...);
        }

        if ($this->traceSet) {
            $methods[\Redis::class]['set'] = $this->traceSet(...);
        }

        if ($this->traceDel) {
            $methods[\Redis::class]['del'] = $this->traceDel(...);
        }

        if ($this->traceDelete) {
            $methods[\Redis::class]['delete'] = $this->traceDel(...);
        }

        if ($this->traceUnlink) {
            $methods[\Redis::class]['unlink'] = $this->traceDel(...);
        }

        if ($this->traceExists) {
            $methods[\Redis::class]['exists'] = $this->traceGet(...);
        }

        return $methods;
    }

    protected function getFunctions(): array
    {
        return [];
    }
}
