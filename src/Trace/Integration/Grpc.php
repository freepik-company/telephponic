<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use Grpc\BaseStub;
use \RuntimeException;

class Grpc extends AbstractIntegration
{
    /** @throws RuntimeException */
    public function __construct()
    {
        if (!extension_loaded('grpc')) {
            throw new RuntimeException('The grpc extension is not loaded');
        }
    }

    public function traceGrpcSimpleRequest(
        BaseStub $stub,
        string $method,
        mixed $argument,
        callable $deserialize,
        array $metadata = [],
        array $options = []
    ): array {
        return $this->generateTraceParams('grpc/request', [
            'grpc.service' => $stub::class,
            'grpc.method' => $this->convertToValue($method),
            'grpc.argument' => $this->convertToValue($argument),
            'grpc.metadata' => $this->convertToValue($metadata),
            'grpc.options' => $this->convertToValue($options),
        ]);
    }

    public function traceGrpcClientStreamRequest(
        BaseStub $stub,
        string $method,
        array $metadata = [],
        array $options = []
    ): array {
        return $this->generateTraceParams('grpc/request', [
            'grpc.service' => $stub::class,
            'grpc.method' => $this->convertToValue($method),
            'grpc.metadata' => $this->convertToValue($metadata),
            'grpc.options' => $this->convertToValue($options),
        ]);
    }

    public function traceGrpcServerStreamRequest(
        BaseStub $stub,
        string $method,
        mixed $argument,
        callable $deserialize,
        array $metadata = [],
        array $options = []
    ): array {
        return $this->generateTraceParams('grpc/request', [
            'grpc.service' => $stub::class,
            'grpc.method' => $this->convertToValue($method),
            'grpc.argument' => $this->convertToValue($argument),
            'grpc.metadata' => $this->convertToValue($metadata),
            'grpc.options' => $this->convertToValue($options),
        ]);
    }

    public function traceGrpcBidiRequest(
        BaseStub $stub,
        string $method,
        callable $deserialize,
        array $metadata = [],
        array $options = []
    ): array {
        return $this->generateTraceParams('grpc/request', [
            'grpc.service' => $stub::class,
            'grpc.method' => $this->convertToValue($method),
            'grpc.metadata' => $this->convertToValue($metadata),
            'grpc.options' => $this->convertToValue($options),
        ]);
    }

    protected function getMethods(): array
    {
        return [
            BaseStub::class => [
                '_simpleRequest' => [$this, ' traceGrpcSimpleRequest'],
                '_clientStreamRequest' => [$this, ' traceGrpcClientStreamRequest'],
                '_serverStreamRequest' => [$this, ' traceGrpcServerStreamRequest'],
                '_bidiRequest' => [$this, ' traceGrpcBidiRequest'],
            ],
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}
