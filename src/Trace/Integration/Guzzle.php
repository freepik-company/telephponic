<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use GuzzleHttp\ClientInterface;

class Guzzle extends AbstractIntegration
{
    private function traceGuzzleRequest(ClientInterface $targetClient, string $method, string $uri, array $args): array
    {
        return $this->generateTraceParams(
            'guzzle/request',
            [
                'guzzle.method' => $this->convertToValue($method),
                'guzzle.uri' => $this->convertToValue($uri),
                'guzzle.args' => $this->convertToValue($args),
                'guzzle.body' => $args['body'] ? $this->convertToValue((string) $args['body']) : null
            ]
        );
    }

    protected function getMethods(): array
    {
        return [
            ClientInterface::class => [
                'request' => $this->traceGuzzleRequest(...)
            ]
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}
