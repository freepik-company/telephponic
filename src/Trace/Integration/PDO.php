<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use JsonException;
use PDO as TargetPDO;
use PDOStatement;
use RuntimeException;

class PDO extends AbstractIntegration
{
    /** @throws RuntimeException */
    public function __construct(
        private bool $tracePdoConnect = true,
        private bool $tracePdoQuery = true,
        private bool $tracePdoCommit = true,
        private bool $tracePdoStatementQuery = true,
        private bool $tracePdoStatementBindParam = true,
    ) {
        if (!extension_loaded('pdo')) {
            throw new RuntimeException('PDO extension is not loaded');
        }
    }

    /** @throws JsonException */
    public function tracePdoConnect(
        TargetPDO $pdo,
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        array $options = []
    ): array {
        return $this->generateTraceParams(
            'pdo/connection',
            [
                'pdo.dsn' => $this->convertToValue($dsn),
                'pdo.username' => $this->convertToValue($username),
                'pdo.options' => $this->convertToValue($options),
                'pdo.instance' => spl_object_hash($pdo),
            ]
        );
    }

    public function tracePdoQuery(
        TargetPDO $pdo,
        string $query,
        ?array $params = null
    ): array {
        return $this->generateTraceParams(
            'pdo/query',
            [
                'pdo.query' => $this->sanitizeQuery($query),
                'pdo.params' => $this->convertToValue($params),
                'pdo.instance' => spl_object_hash($pdo),
            ]
        );
    }

    private function sanitizeQuery(string $query): string
    {
        return $this->convertToValue(
            str_replace(
                "`",
                '',
                $query
            )
        );
    }

    public function tracePdoCommit(TargetPDO $pdo): array
    {
        return $this->generateTraceParams(
            'pdo/commit',
            [
                'pdo.dsn' => $this->convertToValue($pdo->getAttribute(TargetPDO::ATTR_CONNECTION_STATUS)),
                'pdo.username' => $this->convertToValue($pdo->getAttribute(TargetPDO::ATTR_DRIVER_NAME)),
                'pdo.instance' => spl_object_hash($pdo),
            ]
        );
    }

    public function tracePdoStatementQuery(
        PDOStatement $statement,
        ?array $params = null
    ): array {
        return $this->generateTraceParams(
            'pdo/query',
            [
                'pdo.query' => $this->sanitizeQuery($statement->queryString),
                'pdo.params' => $this->convertToValue($params),
                'pdo.statement.instance' => spl_object_hash($statement),
            ]
        );
    }

    public function tracePdoStatementBindParam(
        PDOStatement $statement,
        string|int $param,
        mixed $var,
        int $type = TargetPDO::PARAM_STR,
        int $maxLength = 0,
        mixed $driverOptions = null
    ): array {
        return $this->generateTraceParams(
            'pdo/bind-param',
            [
                'pdo.statement.instance' => spl_object_hash($statement),
                'pdo.bind.param' => $this->convertToValue($param),
                'pdo.bind.value' => $this->convertToValue($var),
                'pdo.bind.type' => $this->mapPdoBindType($type),
                'pdo.bind.max_length' => $this->convertToValue($maxLength),
                'pdo.bind.driver_options' => $this->convertToValue($driverOptions),
            ]
        );
    }

    private function mapPdoBindType(int $type): string
    {
        return match ($type) {
            TargetPDO::PARAM_BOOL => 'bool',
            TargetPDO::PARAM_NULL => 'null',
            TargetPDO::PARAM_INT => 'int',
            TargetPDO::PARAM_STR => 'string',
            TargetPDO::PARAM_LOB => 'lob',
            default => 'unknown',
        };
    }

    protected function getMethods(): array
    {
        $methods = [
            TargetPDO::class => [],
            PDOStatement::class => [],
        ];

        if ($this->tracePdoConnect) {
            $methods[TargetPDO::class]['__construct'] = [$this, 'tracePdoConnect'];
        }

        if ($this->tracePdoQuery) {
            $methods[TargetPDO::class]['exec'] = [$this, 'tracePdoQuery'];
            $methods[TargetPDO::class]['query'] = [$this, 'tracePdoQuery'];
        }

        if ($this->tracePdoCommit) {
            $methods[TargetPDO::class]['commit'] = [$this, 'tracePdoCommit'];
        }

        if ($this->tracePdoStatementQuery) {
            $methods[PDOStatement::class]['execute'] = [$this, 'tracePdoStatementQuery'];
        }

        if ($this->tracePdoStatementBindParam) {
            $methods[PDOStatement::class]['bindParam'] = [$this, 'tracePdoStatementBindParam'];
        }

        return $methods;
    }

    protected function getFunctions(): array
    {
        return [];
    }
}
