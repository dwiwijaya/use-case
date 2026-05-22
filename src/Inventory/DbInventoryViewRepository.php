<?php

declare(strict_types=1);

namespace App\Inventory;

use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbInventoryViewRepository implements InventoryViewRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function getLocations(): array
    {
        return $this->db->createCommand('SELECT id, code, name FROM location ORDER BY name')->queryAll();
    }

    public function getStockRows(?int $locationId = null): array
    {
        $sql = 'SELECT il.id, il.location_id, l.name AS location_name, il.item_id, i.sku, i.name AS item_name,
                u.symbol AS unit_symbol, il.quantity
                FROM item_location il
                INNER JOIN location l ON l.id = il.location_id
                INNER JOIN item i ON i.id = il.item_id
                INNER JOIN unit u ON u.id = i.unit_id';
        $params = [];

        if ($locationId !== null) {
            $sql .= ' WHERE il.location_id = :locationId';
            $params[':locationId'] = $locationId;
        }

        $sql .= ' ORDER BY l.name, i.name';

        return $this->db->createCommand($sql, $params)->queryAll();
    }

    public function getStockByLocation(): array
    {
        return $this->db->createCommand(
            'SELECT l.id, l.name, COUNT(il.id) AS stock_rows, COALESCE(SUM(il.quantity), 0) AS total_quantity
            FROM location l
            LEFT JOIN item_location il ON il.location_id = l.id
            GROUP BY l.id, l.name
            ORDER BY l.name'
        )->queryAll();
    }

    public function getStockForLocation(int $locationId): array
    {
        return $this->db->createCommand(
            'SELECT il.item_id, i.name AS item_name, i.sku, il.quantity, u.symbol AS unit_symbol
            FROM item_location il
            INNER JOIN item i ON i.id = il.item_id
            INNER JOIN unit u ON u.id = i.unit_id
            WHERE il.location_id = :locationId
            ORDER BY i.name',
            [':locationId' => $locationId]
        )->queryAll();
    }

    public function getSummary(): array
    {
        return [
            'locations' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM location')->queryScalar() ?? 0),
            'stock_rows' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM item_location')->queryScalar() ?? 0),
            'total_quantity' => (int) ($this->db->createCommand('SELECT COALESCE(SUM(quantity), 0) FROM item_location')->queryScalar() ?? 0),
        ];
    }
}
