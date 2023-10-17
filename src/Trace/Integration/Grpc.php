<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use Grpc\BaseStub;

class Grpc extends AbstractIntegration
{
    public function __construct(
        private readonly bool $traceGrpcSimpleRequest = true,
        private readonly bool $traceGrpcClientStreamRequest = true,
        private readonly bool $traceGrpcServerStreamRequest = true,
        private readonly bool $traceGrpcBidiRequest = true
    ) {
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
        $methods = [
            BaseStub::class => [],
        ];

        if ($this->traceGrpcSimpleRequest) {
            $methods[BaseStub::class]['_simpleRequest'] = [$this, ' traceGrpcSimpleRequest'];
        }

        if ($this->traceGrpcClientStreamRequest) {
            $methods[BaseStub::class]['_clientStreamRequest'] = [$this, ' traceGrpcClientStreamRequest'];
        }

        if ($this->traceGrpcServerStreamRequest) {
            $methods[BaseStub::class]['_serverStreamRequest'] = [$this, ' traceGrpcServerStreamRequest'];
        }

        if ($this->traceGrpcBidiRequest) {
            $methods[BaseStub::class]['_bidiRequest'] = [$this, ' traceGrpcBidiRequest'];
        }

        return $methods;
    }

    protected function getFunctions(): array
    {
        return [];
    }
}
