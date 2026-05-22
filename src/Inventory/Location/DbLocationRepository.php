<?php

declare(strict_types=1);

namespace App\Inventory\Location;

use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbLocationRepository implements LocationRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function findById(int $id): ?Location
    {
        $row = $this->db->createCommand(
            'SELECT id, code, name FROM location WHERE id = :id',
            [':id' => $id]
        )->queryOne();

        if ($row === null) {
            return null;
        }

        return new Location(
            (int) $row['id'],
            (string) $row['code'],
            (string) $row['name'],
        );
    }

    public function save(Location $location): void
    {
        $row = $this->db->createCommand(
            'SELECT id FROM location WHERE code = :code AND (:id IS NULL OR id != :id)',
            [':code' => $location->code, ':id' => $location->id]
        )->queryOne();

        if ($row !== null) {
            throw new DomainException('Kode lokasi harus unik.');
        }

        $payload = ['code' => $location->code, 'name' => $location->name];
        if ($location->id === null) {
            $this->db->createCommand()->insert('location', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update('location', $payload, 'id = :id', [':id' => $location->id])->execute();
    }

    public function delete(int $id): void
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
}
