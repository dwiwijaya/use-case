<?php

declare(strict_types=1);

namespace App\Catalog;

use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class CatalogRepository
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    /**
     * @return list<array{id:string,name:string,symbol:string}>
     */
    public function getUnits(): array
    {
        return $this->db->createCommand('SELECT id, name, symbol FROM unit ORDER BY name')->queryAll();
    }

    /**
     * @return list<array{id:string,sku:string,name:string,unit_id:string,unit_name:string,unit_symbol:string}>
     */
    public function getItems(): array
    {
        return $this->db->createCommand(
            'SELECT i.id, i.sku, i.name, i.unit_id, u.name AS unit_name, u.symbol AS unit_symbol
            FROM item i
            INNER JOIN unit u ON u.id = i.unit_id
            ORDER BY i.name'
        )->queryAll();
    }

    /**
     * @return array{items:int,units:int}
     */
    public function getSummary(): array
    {
        return [
            'items' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM item')->queryScalar() ?? 0),
            'units' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM unit')->queryScalar() ?? 0),
        ];
    }

    /**
     * @return array{id:string,name:string,symbol:string}|null
     */
    public function findUnit(int $id): ?array
    {
        return $this->db->createCommand(
            'SELECT id, name, symbol FROM unit WHERE id = :id',
            [':id' => $id]
        )->queryOne();
    }

    /**
     * @return array{id:string,sku:string,name:string,unit_id:string}|null
     */
    public function findItem(int $id): ?array
    {
        return $this->db->createCommand(
            'SELECT id, sku, name, unit_id FROM item WHERE id = :id',
            [':id' => $id]
        )->queryOne();
    }

    public function saveUnit(?int $id, string $name, string $symbol): void
    {
        $this->assertUniqueUnit($id, $name, $symbol);

        $payload = ['name' => $name, 'symbol' => $symbol];
        if ($id === null) {
            $this->db->createCommand()->insert('unit', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update('unit', $payload, 'id = :id', [':id' => $id])->execute();
    }

    public function saveItem(?int $id, string $sku, string $name, int $unitId): void
    {
        $this->assertUniqueItem($id, $sku);

        $payload = ['sku' => $sku, 'name' => $name, 'unit_id' => $unitId];
        if ($id === null) {
            $this->db->createCommand()->insert('item', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update('item', $payload, 'id = :id', [':id' => $id])->execute();
    }

    public function deleteUnit(int $id): void
    {
        $inUse = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM item WHERE unit_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);

        if ($inUse > 0) {
            throw new DomainException('Unit masih dipakai oleh item, jadi belum bisa dihapus.');
        }

        $this->db->createCommand()->delete('unit', 'id = :id', [':id' => $id])->execute();
    }

    public function deleteItem(int $id): void
    {
        $stockUsage = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM item_location WHERE item_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);
        $orderUsage = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM order_item WHERE item_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);

        if ($stockUsage > 0 || $orderUsage > 0) {
            throw new DomainException('Item sudah dipakai di stok atau transaksi, jadi tidak bisa dihapus.');
        }

        $this->db->createCommand()->delete('item', 'id = :id', [':id' => $id])->execute();
    }

    private function assertUniqueUnit(?int $id, string $name, string $symbol): void
    {
        $row = $this->db->createCommand(
            'SELECT id FROM unit WHERE (name = :name OR symbol = :symbol) AND (:id IS NULL OR id != :id)',
            [':name' => $name, ':symbol' => $symbol, ':id' => $id]
        )->queryOne();

        if ($row !== null) {
            throw new DomainException('Nama atau simbol unit sudah dipakai.');
        }
    }

    private function assertUniqueItem(?int $id, string $sku): void
    {
        $row = $this->db->createCommand(
            'SELECT id FROM item WHERE sku = :sku AND (:id IS NULL OR id != :id)',
            [':sku' => $sku, ':id' => $id]
        )->queryOne();

        if ($row !== null) {
            throw new DomainException('SKU item harus unik.');
        }
    }
}
