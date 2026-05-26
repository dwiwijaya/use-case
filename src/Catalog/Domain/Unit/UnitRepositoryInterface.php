<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Unit;

interface UnitRepositoryInterface
{
    public function findById(int $id): ?Unit;

    public function existsByNameOrSymbol(string $name, string $symbol, ?int $excludeId = null): bool;

    public function isInUse(int $id): bool;

    public function save(Unit $unit): void;

    public function delete(int $id): void;
}
