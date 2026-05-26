<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence;

use App\Catalog\Domain\Unit\Unit;
use App\Catalog\Domain\Unit\UnitRepositoryInterface;
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
        $payload = [
            'name' => $unit->name,
            'symbol' => $unit->symbol,
        ];

        if ($unit->id === null) {
            $this->db->createCommand()->insert('unit', $payload)->execute();
            return;
        }

        $this->db->createCommand()->update(
            table: 'unit',
            columns: $payload,
            condition: 'id = :id',
            params: [':id' => $unit->id],
        )->execute();
    }

    public function existsByNameOrSymbol(string $name, string $symbol, ?int $excludeId = null): bool
    {
        $row = $this->db->createCommand(
            'SELECT id FROM unit WHERE (name = :name OR symbol = :symbol) AND (:id IS NULL OR id != :id)',
            [':name' => $name, ':symbol' => $symbol, ':id' => $excludeId]
        )->queryOne();

        return $row !== null;
    }

    public function isInUse(int $id): bool
    {
        $inUse = (int) ($this->db->createCommand(
            'SELECT COUNT(*) FROM item WHERE unit_id = :id',
            [':id' => $id]
        )->queryScalar() ?? 0);

        return $inUse > 0;
    }

    public function delete(int $id): void
    {
        $this->db->createCommand()->delete('unit', 'id = :id', [':id' => $id])->execute();
    }
}
