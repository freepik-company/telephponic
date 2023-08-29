<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use JsonException;

class Curl extends AbstractIntegration
{

    public function traceCurlInit(
        ?string $url = null,
    ): array {
        return [
            'type' => 'curl/open',
            'curl.url' => $url ?? 'Not set',
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

    public function traceCurlSetOpt(
        mixed $handler,
        int $option,
        mixed $value
    ): array {
        $optionName = $this->getCurlOptName($option);
        $optionValue = $this->getCurlOptValue($value);

        return [
            'type' => 'curl/setopt',
            'curl.handler' => sprintf("resource curl#%d", (int)$handler),
            'curl.option' => $optionName,
            'curl.value' => $optionValue,
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
            CURLOPT_FTPASCII => 'CURLOPT_FTPASCII',
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
            CURLOPT_MUTE => 'CURLOPT_MUTE',
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

    private function getCurlOptValue(mixed $value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string)$value;
        }

        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value
                ? 'true'
                : 'false';
        }

        if (is_resource($value)) {
            return sprintf("resource %s#%d", get_resource_type($value), (int)$value);
        }

        if (is_array($value) || is_object($value)) {
            try {
                return json_encode($value, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // Do nothing. Will return 'Unknown value'
            }
        }

        return 'Unknown value';
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
            'curl_setopt' => [$this, 'traceCurlSetOpt'],
        ];
    }

}