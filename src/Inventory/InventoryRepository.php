<?php

declare(strict_types=1);

namespace App\Inventory;

use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class InventoryRepository
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    /**
     * @return list<array{id:string,code:string,name:string}>
     */
    public function getLocations(): array
    {
        return $this->db->createCommand('SELECT id, code, name FROM location ORDER BY name')->queryAll();
    }

    /**
     * @return list<array{id:string,location_id:string,location_name:string,item_id:string,sku:string,item_name:string,unit_symbol:string,quantity:string}>
     */
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

    /**
     * @return list<array{id:string,name:string,stock_rows:string,total_quantity:string}>
     */
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

    /**
     * @return array{locations:int,stock_rows:int,total_quantity:int}
     */
    public function getSummary(): array
    {
        return [
            'locations' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM location')->queryScalar() ?? 0),
            'stock_rows' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM item_location')->queryScalar() ?? 0),
            'total_quantity' => (int) ($this->db->createCommand('SELECT COALESCE(SUM(quantity), 0) FROM item_location')->queryScalar() ?? 0),
        ];
    }

    /**
     * @return array{id:string,code:string,name:string}|null
     */
    public function findLocation(int $id): ?array
    {
        return $this->db->createCommand(
            'SELECT id, code, name FROM location WHERE id = :id',
            [':id' => $id]
        )->queryOne();
    }

    public function saveLocation(?int $id, string $code, string $name): void
    {
        $row = $this->db->createCommand(
            'SELECT id FROM location WHERE code = :code AND (:id IS NULL OR id != :id)',
            [':code' => $code, ':id' => $id]
        )->queryOne();

        if ($row !== null) {
            throw new DomainException('Kode lokasi harus unik.');
        }

        $payload = ['code' => $code, 'name' => $name];
        if ($id === null) {
            $this->db->createCommand()->insert('location', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update('location', $payload, 'id = :id', [':id' => $id])->execute();
    }

    public function deleteLocation(int $id): void
    {
        $stockUsage = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM item_location WHERE location_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);
        $orderUsage = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM sales_order WHERE location_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);

        if ($stockUsage > 0 || $orderUsage > 0) {
            throw new DomainException('Lokasi sudah dipakai oleh stok atau transaksi.');
        }

        $this->db->createCommand()->delete('location', 'id = :id', [':id' => $id])->execute();
    }

    public function setStock(int $locationId, int $itemId, int $quantity): void
    {
        $existing = $this->db->createCommand(
            'SELECT id FROM item_location WHERE location_id = :locationId AND item_id = :itemId',
            [':locationId' => $locationId, ':itemId' => $itemId]
        )->queryOne();

        if ($existing === null) {
            $this->db->createCommand()->insert('item_location', [
                'location_id' => $locationId,
                'item_id' => $itemId,
                'quantity' => $quantity,
            ])->execute();
            return;
        }

        $this->db->createCommand()->update(
            'item_location',
            ['quantity' => $quantity],
            'id = :id',
            [':id' => (int) $existing['id']]
        )->execute();
    }

    public function deleteStockRow(int $id): void
    {
        $this->db->createCommand()->delete('item_location', 'id = :id', [':id' => $id])->execute();
    }

    /**
     * @return list<array{item_id:string,item_name:string,sku:string,quantity:string,unit_symbol:string}>
     */
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
}
