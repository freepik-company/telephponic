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
        return [
            'type' => 'pdo/connection',
            'pdo.dsn' => $dsn,
            'pdo.username' => $username,
            'pdo.options' => json_encode($options, JSON_THROW_ON_ERROR),
        ];
    }

    public function tracePdoQuery(
        TargetPDO $pdo,
        string $query,
        ?array $params = null
    ): array {
        return [
            'type' => 'pdo/query',
            'pdo.query' => $query,
            'pdo.params' => json_encode($params, JSON_THROW_ON_ERROR),
        ];
    }

    public function tracePdoCommit(TargetPDO $pdo): array
    {
        return [
            'type' => 'pdo/commit',
        ];
    }

    public function tracePdoStatementQuery(
        PDOStatement $statement,
        ?array $params = null
    ): array {
        return [
            'type' => 'pdo/query',
            'pdo.query' => $statement->queryString,
            'pdo.params' => json_encode($params, JSON_THROW_ON_ERROR),
        ];
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