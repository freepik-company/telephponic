<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use RuntimeException;

class Curl extends AbstractIntegration
{
    /** @throws RuntimeException */
    public function __construct(
        private readonly bool $traceCurlInit = true,
        private readonly bool $traceCurlExec = true,
        private readonly bool $traceCurlSetOpt = true
    ) {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('Curl extension is not loaded');
        }
    }

    public function traceCurlInit(?string $url = null): array
    {
        return $this->generateTraceParams('curl/open', [
            'curl.url' => $this->convertToValue($url),
        ]);
    }

    public function traceCurlExec($ch): array
    {
        $curlInfo = curl_getinfo($ch);
        $curlInfo['instance'] = 'curl handler #' . (int)$ch;
        $curlInfo['url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $params = !$curlInfo
            ? []
            : array_merge(
                ...array_map(
                    fn($key, $value) => [
                        sprintf('curl.%s', $key) => $this->convertToValue($value),
                    ],
                    array_keys($curlInfo),
                    $curlInfo
                )
            );

        return $this->generateTraceParams('curl/exec', $params);
    }

    public function traceCurlSetOpt(
        mixed $handler,
        int $option,
        mixed $value
    ): array {
        return [
            'type' => 'curl/setopt',
            'curl.handler' => sprintf("resource curl#%d", (int)$handler),
            'curl.option' => $this->getCurlOptName($option),
            'curl.value' => $this->convertToValue($value),
        ];
    }

    /**
     * @param int $option
     *
     * @return string
     */
    public function getCurlOptName(int $option): string
    {
        return match ($option) {
            CURLOPT_ADDRESS_SCOPE => 'CURLOPT_ADDRESS_SCOPE',
            CURLOPT_AUTOREFERER => 'CURLOPT_AUTOREFERER',
            CURLOPT_BINARYTRANSFER => 'CURLOPT_BINARYTRANSFER',
            CURLOPT_BUFFERSIZE => 'CURLOPT_BUFFERSIZE',
            CURLOPT_CERTINFO => 'CURLOPT_CERTINFO',
            CURLOPT_CONNECTTIMEOUT => 'CURLOPT_CONNECTTIMEOUT',
            CURLOPT_CONNECT_ONLY => 'CURLOPT_CONNECT_ONLY',
            CURLOPT_COOKIESESSION => 'CURLOPT_COOKIESESSION',
            CURLOPT_CRLF => 'CURLOPT_CRLF',
            CURLOPT_CRLFILE => 'CURLOPT_CRLFILE',
            CURLOPT_DNS_CACHE_TIMEOUT => 'CURLOPT_DNS_CACHE_TIMEOUT',
            CURLOPT_DNS_USE_GLOBAL_CACHE => 'CURLOPT_DNS_USE_GLOBAL_CACHE',
            CURLOPT_FAILONERROR => 'CURLOPT_FAILONERROR',
            CURLOPT_FILETIME => 'CURLOPT_FILETIME',
            CURLOPT_FOLLOWLOCATION => 'CURLOPT_FOLLOWLOCATION',
            CURLOPT_FORBID_REUSE => 'CURLOPT_FORBID_REUSE',
            CURLOPT_FRESH_CONNECT => 'CURLOPT_FRESH_CONNECT',
            CURLOPT_FTPAPPEND => 'CURLOPT_FTPAPPEND',
            CURLOPT_FTPLISTONLY => 'CURLOPT_FTPLISTONLY',
            CURLOPT_FTPSSLAUTH => 'CURLOPT_FTPSSLAUTH',
            CURLOPT_FTP_CREATE_MISSING_DIRS => 'CURLOPT_FTP_CREATE_MISSING_DIRS',
            CURLOPT_FTP_USE_EPRT => 'CURLOPT_FTP_USE_EPRT',
            CURLOPT_FTP_USE_EPSV => 'CURLOPT_FTP_USE_EPSV',
            CURLOPT_HEADER => 'CURLOPT_HEADER',
            CURLOPT_HTTPAUTH => 'CURLOPT_HTTPAUTH',
            CURLOPT_HTTPGET => 'CURLOPT_HTTPGET',
            CURLOPT_HTTPPROXYTUNNEL => 'CURLOPT_HTTPPROXYTUNNEL',
            CURLOPT_HTTP_VERSION => 'CURLOPT_HTTP_VERSION',
            CURLOPT_INFILESIZE => 'CURLOPT_INFILESIZE',
            CURLOPT_IPRESOLVE => 'CURLOPT_IPRESOLVE',
            CURLOPT_ISSUERCERT => 'CURLOPT_ISSUERCERT',
            CURLOPT_KEYPASSWD => 'CURLOPT_KEYPASSWD',
            CURLOPT_KRB4LEVEL => 'CURLOPT_KRB4LEVEL',
            CURLOPT_LOGIN_OPTIONS => 'CURLOPT_LOGIN_OPTIONS',
            CURLOPT_LOW_SPEED_LIMIT => 'CURLOPT_LOW_SPEED_LIMIT',
            CURLOPT_LOW_SPEED_TIME => 'CURLOPT_LOW_SPEED_TIME',
            CURLOPT_MAXCONNECTS => 'CURLOPT_MAXCONNECTS',
            CURLOPT_MAXFILESIZE => 'CURLOPT_MAXFILESIZE',
            CURLOPT_MAXREDIRS => 'CURLOPT_MAXREDIRS',
            CURLOPT_MAX_RECV_SPEED_LARGE => 'CURLOPT_MAX_RECV_SPEED_LARGE',
            CURLOPT_MAX_SEND_SPEED_LARGE => 'CURLOPT_MAX_SEND_SPEED_LARGE',
            CURLOPT_NETRC => 'CURLOPT_NETRC',
            CURLOPT_NOBODY => 'CURLOPT_NOBODY',
            CURLOPT_NOPROGRESS => 'CURLOPT_NOPROGRESS',
            CURLOPT_NOSIGNAL => 'CURLOPT_NOSIGNAL',
            CURLOPT_PORT => 'CURLOPT_PORT',
            CURLOPT_POST => 'CURLOPT_POST',
            CURLOPT_PROTOCOLS => 'CURLOPT_PROTOCOLS',
            CURLOPT_PROXYAUTH => 'CURLOPT_PROXYAUTH',
            CURLOPT_PROXYPORT => 'CURLOPT_PROXYPORT',
            CURLOPT_PROXYTYPE => 'CURLOPT_PROXYTYPE',
            CURLOPT_PROXY_CAINFO => 'CURLOPT_PROXY_CAINFO',
            CURLOPT_PROXY_CAPATH => 'CURLOPT_PROXY_CAPATH',
            CURLOPT_PROXY_CRLFILE => 'CURLOPT_PROXY_CRLFILE',
            CURLOPT_PROXY_KEYPASSWD => 'CURLOPT_PROXY_KEYPASSWD',
            CURLOPT_PROXY_SSLCERT => 'CURLOPT_PROXY_SSLCERT',
            CURLOPT_PROXY_SSLCERTTYPE => 'CURLOPT_PROXY_SSLCERTTYPE',
            CURLOPT_PROXY_SSLKEY => 'CURLOPT_PROXY_SSLKEY',
            CURLOPT_PROXY_SSLKEYTYPE => 'CURLOPT_PROXY_SSLKEYTYPE',
            CURLOPT_PROXY_SSLVERSION => 'CURLOPT_PROXY_SSLVERSION',
            CURLOPT_PROXY_TRANSFER_MODE => 'CURLOPT_PROXY_TRANSFER_MODE',
            CURLOPT_PUT => 'CURLOPT_PUT',
            CURLOPT_REDIR_PROTOCOLS => 'CURLOPT_REDIR_PROTOCOLS',
            CURLOPT_RESUME_FROM => 'CURLOPT_RESUME_FROM',
            CURLOPT_RETURNTRANSFER => 'CURLOPT_RETURNTRANSFER',
            CURLOPT_SSH_AUTH_TYPES => 'CURLOPT_SSH_AUTH_TYPES',
            CURLOPT_SSH_HOST_PUBLIC_KEY_MD5 => 'CURLOPT_SSH_HOST_PUBLIC_KEY_MD5',
            CURLOPT_SSH_KNOWNHOSTS => 'CURLOPT_SSH_KNOWNHOSTS',
            CURLOPT_SSH_PRIVATE_KEYFILE => 'CURLOPT_SSH_PRIVATE_KEYFILE',
            CURLOPT_SSH_PUBLIC_KEYFILE => 'CURLOPT_SSH_PUBLIC_KEYFILE',
            CURLOPT_SSLCERT => 'CURLOPT_SSLCERT',
            CURLOPT_SSLCERTPASSWD => 'CURLOPT_SSLCERTPASSWD',
            CURLOPT_SSLCERTTYPE => 'CURLOPT_SSLCERTTYPE',
            CURLOPT_SSLKEY => 'CURLOPT_SSLKEY',
            CURLOPT_SSLKEYPASSWD => 'CURLOPT_SSLKEYPASSWD',
            CURLOPT_SSLKEYTYPE => 'CURLOPT_SSLKEYTYPE',
            CURLOPT_SSLVERSION => 'CURLOPT_SSLVERSION',
            CURLOPT_SSL_CIPHER_LIST => 'CURLOPT_SSL_CIPHER_LIST',
            CURLOPT_SSL_OPTIONS => 'CURLOPT_SSL_OPTIONS',
            CURLOPT_SSL_SESSIONID_CACHE => 'CURLOPT_SSL_SESSIONID_CACHE',
            CURLOPT_SSL_VERIFYHOST => 'CURLOPT_SSL_VERIFYHOST',
            CURLOPT_SSL_VERIFYPEER => 'CURLOPT_SSL_VERIFYPEER',
            CURLOPT_SSL_VERIFYSTATUS => 'CURLOPT_SSL_VERIFYSTATUS',
            CURLOPT_TCP_NODELAY => 'CURLOPT_TCP_NODELAY',
            CURLOPT_TIMECONDITION => 'CURLOPT_TIMECONDITION',
            CURLOPT_TIMEOUT => 'CURLOPT_TIMEOUT',
            CURLOPT_TIMEVALUE => 'CURLOPT_TIMEVALUE',
            CURLOPT_TRANSFERTEXT => 'CURLOPT_TRANSFERTEXT',
            CURLOPT_UNRESTRICTED_AUTH => 'CURLOPT_UNRESTRICTED_AUTH',
            CURLOPT_UPLOAD => 'CURLOPT_UPLOAD',
            CURLOPT_URL => 'CURLOPT_URL',
            CURLOPT_USERAGENT => 'CURLOPT_USERAGENT',
            CURLOPT_USERNAME => 'CURLOPT_USERNAME',
            CURLOPT_USERPWD => 'CURLOPT_USERPWD',
            CURLOPT_USE_SSL => 'CURLOPT_USE_SSL',
            CURLOPT_VERBOSE => 'CURLOPT_VERBOSE',
            default => 'Unknown cURL option',
        };
    }

    protected function getMethods(): array
    {
        return [];
    }

    protected function getFunctions(): array
    {
        $functions = [];

        if ($this->traceCurlInit) {
            $functions['curl_init'] = [$this, 'traceCurlInit'];
        }

        if ($this->traceCurlExec) {
            $functions['curl_exec'] = [$this, 'traceCurlExec'];
        }

        if ($this->traceCurlSetOpt) {
            $functions['curl_setopt'] = [$this, 'traceCurlSetOpt'];
        }

        return $functions;
    }
}
