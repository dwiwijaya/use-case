<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * @psalm-var list<callable(ContainerInterface): void>
 */
return [
    static function (ContainerInterface $container): void {
        $container
            ->get(ConnectionInterface::class)
            ->createCommand('PRAGMA foreign_keys = ON')
            ->execute();
    },
];
