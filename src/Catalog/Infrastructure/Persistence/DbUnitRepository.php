<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence;

use App\Catalog\Domain\Unit\Unit;
use App\Catalog\Domain\Unit\UnitRepositoryInterface;
use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbUnitRepository implements UnitRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function findById(int $id): ?Unit
    {
        $row = $this->db->createCommand(
            'SELECT id, name, symbol FROM unit WHERE id = :id',
            [':id' => $id]
        )->queryOne();

        if ($row === null) {
            return null;
        }

        return new Unit(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['symbol'],
        );
    }

    public function save(Unit $unit): void
    {
        $this->assertUnique($unit);

        $payload = [
            'name' => $unit->name,
            'symbol' => $unit->symbol,
        ];

        if ($unit->id === null) {
            $this->db->createCommand()->insert('unit', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update('unit', $payload, 'id = :id', [':id' => $unit->id])->execute();
    }

    public function delete(int $id): void
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

    private function assertUnique(Unit $unit): void
    {
        $row = $this->db->createCommand(
            'SELECT id FROM unit WHERE (name = :name OR symbol = :symbol) AND (:id IS NULL OR id != :id)',
            [':name' => $unit->name, ':symbol' => $unit->symbol, ':id' => $unit->id]
        )->queryOne();

        if ($row !== null) {
            throw new DomainException('Nama atau simbol unit sudah dipakai.');
        }
    }
}
