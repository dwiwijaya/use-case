<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence;

use App\Catalog\Domain\Item\Item;
use App\Catalog\Domain\Item\ItemRepositoryInterface;
use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbItemRepository implements ItemRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function findById(int $id): ?Item
    {
        $row = $this->db->createCommand(
            'SELECT id, sku, name, unit_id FROM item WHERE id = :id',
            [':id' => $id]
        )->queryOne();

        if ($row === null) {
            return null;
        }

        return new Item(
            (int) $row['id'],
            (string) $row['sku'],
            (string) $row['name'],
            (int) $row['unit_id'],
        );
    }

    public function save(Item $item): void
    {
        $this->assertUniqueSku($item);
        $this->assertUnitExists($item->unitId);

        $payload = [
            'sku' => $item->sku,
            'name' => $item->name,
            'unit_id' => $item->unitId,
        ];

        if ($item->id === null) {
            $this->db->createCommand()->insert('item', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update('item', $payload, 'id = :id', [':id' => $item->id])->execute();
    }

    public function delete(int $id): void
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

    private function assertUniqueSku(Item $item): void
    {
        $row = $this->db->createCommand(
            'SELECT id FROM item WHERE sku = :sku AND (:id IS NULL OR id != :id)',
            [':sku' => $item->sku, ':id' => $item->id]
        )->queryOne();

        if ($row !== null) {
            throw new DomainException('SKU item harus unik.');
        }
    }

    private function assertUnitExists(int $unitId): void
    {
        $exists = $this->db->createCommand(
            'SELECT id FROM unit WHERE id = :id',
            [':id' => $unitId]
        )->queryScalar();

        if ($exists === null || $exists === false) {
            throw new DomainException('Unit item tidak ditemukan.');
        }
    }
}
