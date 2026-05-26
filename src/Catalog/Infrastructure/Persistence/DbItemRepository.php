<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence;

use App\Catalog\Domain\Item\Item;
use App\Catalog\Domain\Item\ItemRepositoryInterface;
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
        $payload = [
            'sku' => $item->sku,
            'name' => $item->name,
            'unit_id' => $item->unitId,
        ];

        if ($item->id === null) {
            $this->db->createCommand()->insert('item', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update(
            table: 'item',
            columns: $payload,
            condition: 'id = :id',
            params: [':id' => $item->id],
        )->execute();
    }

    public function existsBySku(string $sku, ?int $excludeId = null): bool
    {
        $row = $this->db->createCommand(
            'SELECT id FROM item WHERE sku = :sku AND (:id IS NULL OR id != :id)',
            [':sku' => $sku, ':id' => $excludeId]
        )->queryOne();

        return $row !== null;
    }

    public function unitExists(int $unitId): bool
    {
        $exists = $this->db->createCommand(
            'SELECT id FROM unit WHERE id = :id',
            [':id' => $unitId]
        )->queryScalar();

        return $exists !== null && $exists !== false;
    }

    public function isInUse(int $id): bool
    {
        $stockUsage = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM item_location WHERE item_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);
        $orderUsage = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM order_item WHERE item_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);

        return $stockUsage > 0 || $orderUsage > 0;
    }

    public function delete(int $id): void
    {
        $this->db->createCommand()->delete('item', 'id = :id', [':id' => $id])->execute();
    }
}
