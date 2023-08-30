<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Integration;

use JsonException;
use PDO as TargetPDO;
use PDOStatement;

class PDO extends AbstractIntegration
{
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
            ]
        );
    }

    protected function getMethods(): array
    {
        return [
            TargetPDO::class => [
                'exec' => [$this, 'tracePdoQuery',],
                'query' => [$this, 'tracePdoQuery',],
                'commit' => [$this, 'tracePdoCommit'],
                '__construct' => [$this, 'tracePdoConnect',],
            ],
            PDOStatement::class => [
                'execute' => [$this, 'tracePdoStatementQuery'],
            ],
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}