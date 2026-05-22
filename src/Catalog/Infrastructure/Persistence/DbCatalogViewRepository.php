<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence;

use App\Catalog\ReadModel\CatalogViewRepositoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbCatalogViewRepository implements CatalogViewRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function getUnits(): array
    {
        return $this->db->createCommand('SELECT id, name, symbol FROM unit ORDER BY name')->queryAll();
    }

    public function getItems(): array
    {
        return $this->db->createCommand(
            'SELECT i.id, i.sku, i.name, i.unit_id, u.name AS unit_name, u.symbol AS unit_symbol
            FROM item i
            INNER JOIN unit u ON u.id = i.unit_id
            ORDER BY i.name'
        )->queryAll();
    }

    public function getSummary(): array
    {
        return [
            'items' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM item')->queryScalar() ?? 0),
            'units' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM unit')->queryScalar() ?? 0),
        ];
    }
}
