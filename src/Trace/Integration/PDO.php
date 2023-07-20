<?php

declare(strict_types=1);

namespace Muriano\Telephponic\Trace\Integration;

use PDO as PDOPdo;
use PDOStatement;

class PDO extends AbstractIntegration
{
    protected function getMethods(): array
    {
        return [
            PDOPdo::class => [
                'exec' => ['type' => 'pdo/query',],
                'query' => ['type' => 'pdo/query',],
                'commit' => ['type' => 'pdo/query',],
                '__construct' => ['type' => 'pdo/connection',],
            ],
            PDOStatement::class => [
                'execute' => ['type' => 'pdo/query',],
            ],
        ];
    }

    protected function getFunctions(): array
    {
        return [];
    }
}