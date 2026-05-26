<?php

declare(strict_types=1);

namespace App\Inventory\Stock\Infrastructure\Persistence;

use App\Inventory\Stock\Domain\Stock;
use App\Inventory\Stock\Domain\StockRepositoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbStockRepository implements StockRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function save(Stock $stock): void
    {
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
            table: 'item_location',
            columns: ['quantity' => $stock->quantity],
            condition: 'id = :id',
            params: [':id' => (int) $existing['id']]
        )->execute();
    }

    public function delete(int $id): void
    {
        $this->db->createCommand()->delete('item_location', 'id = :id', [':id' => $id])->execute();
    }

    public function locationExists(int $locationId): bool
    {
        $exists = $this->db->createCommand(
            'SELECT id FROM location WHERE id = :id',
            [':id' => $locationId]
        )->queryScalar();

        return $exists !== null && $exists !== false;
    }

    public function itemExists(int $itemId): bool
    {
        $exists = $this->db->createCommand(
            'SELECT id FROM item WHERE id = :id',
            [':id' => $itemId]
        )->queryScalar();

        return $exists !== null && $exists !== false;
    }
}
