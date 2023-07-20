<?php

declare(strict_types=1);

namespace Muriano\Telephponic\Trace\Integration;

class Curl extends AbstractIntegration
{

    protected function getMethods(): array
    {
        return [];
    }

    protected function getFunctions(): array
    {
        return [
            'curl_init' => ['type' => 'curl/open',],
            'curl_exec' => ['type' => 'curl/request',],
            'curl_close' => ['type' => 'curl/close',],
            'curl_multi_init' => ['type' => 'curl/open',],
            'curl_multi_exec' => ['type' => 'curl/request',],
            'curl_multi_close' => ['type' => 'curl/close',],
            'curl_multi_add_handle' => ['type' => 'curl/handle-resources',],
            'curl_multi_remove_handle' => ['type' => 'curl/handle-resources',],
        ];
    }
}