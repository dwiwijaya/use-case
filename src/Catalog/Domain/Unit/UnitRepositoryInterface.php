<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Unit;

interface UnitRepositoryInterface
{
    public function findById(int $id): ?Unit;

    public function save(Unit $unit): void;

    public function delete(int $id): void;
}
