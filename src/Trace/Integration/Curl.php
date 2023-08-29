<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

class Curl extends AbstractIntegration
{

    public function traceCurlInit(
        string $url
    ): array {
        return [
            'type' => 'curl/open',
            'curl.url' => $url,
        ];
    }

    public function traceCurlExec(): array
    {
        return [
            'type' => 'curl/request',
        ];
    }

    public function traceCurlClose(): array
    {
        return [
            'type' => 'curl/close',
        ];
    }

    public function traceCurlMultiInit(): array
    {
        return [
            'type' => 'curl/open',
        ];
    }

    protected function getMethods(): array
    {
        return [];
    }

    protected function getFunctions(): array
    {
        return [
            'curl_init' => [$this, 'traceCurlInit'],
            'curl_exec' => [$this, 'traceCurlExec'],
            'curl_multi_init' => [$this, 'traceCurlMultiInit'],
            'curl_multi_exec' => [$this, 'traceCurlExec'],
        ];
    }

}