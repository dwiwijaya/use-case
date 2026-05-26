<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Unit;

use DomainException;

final readonly class UnitService
{
    public function __construct(
        private UnitRepositoryInterface $units,
    ) {}

    public function save(UnitInput $input): void
    {
        $unit = $input->toEntity();

        if ($this->units->existsByNameOrSymbol($unit->name, $unit->symbol, $unit->id)) {
            throw new DomainException('Nama atau simbol unit sudah dipakai.');
        }

        $this->units->save($unit);
    }

    public function delete(int $id): void
    {
        if ($this->units->isInUse($id)) {
            throw new DomainException('Unit masih dipakai oleh item, jadi belum bisa dihapus.');
        }

        $this->units->delete($id);
    }
}
