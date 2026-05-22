<?php

declare(strict_types=1);

namespace App\Inventory\Stock;

use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbStockRepository implements StockRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function save(Stock $stock): void
    {
        $this->assertLocationExists($stock->locationId);
        $this->assertItemExists($stock->itemId);

        $existing = $this->db->createCommand(
            'SELECT id FROM item_location WHERE location_id = :locationId AND item_id = :itemId',
            [':locationId' => $stock->locationId, ':itemId' => $stock->itemId]
        )->queryOne();

        if ($existing === null) {
            $this->db->createCommand()->insert('item_location', [
                'location_id' => $stock->locationId,
                'item_id' => $stock->itemId,
                'quantity' => $stock->quantity,
            ])->execute();
            return;
        }

        $this->db->createCommand()->update(
            'item_location',
            ['quantity' => $stock->quantity],
            'id = :id',
            [':id' => (int) $existing['id']]
        )->execute();
    }

    public function delete(int $id): void
    {
        $this->db->createCommand()->delete('item_location', 'id = :id', [':id' => $id])->execute();
    }

    private function assertLocationExists(int $locationId): void
    {
        $exists = $this->db->createCommand(
            'SELECT id FROM location WHERE id = :id',
            [':id' => $locationId]
        )->queryScalar();

        if ($exists === null || $exists === false) {
            throw new DomainException('Lokasi stok tidak ditemukan.');
        }
    }

    private function assertItemExists(int $itemId): void
    {
        $exists = $this->db->createCommand(
            'SELECT id FROM item WHERE id = :id',
            [':id' => $itemId]
        )->queryScalar();

        if ($exists === null || $exists === false) {
            throw new DomainException('Item stok tidak ditemukan.');
        }
    }
}
