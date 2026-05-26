<?php

declare(strict_types=1);

namespace App\Inventory\Location\Domain;

interface LocationRepositoryInterface
{
    public function findById(int $id): ?Location;

    public function existsByCode(string $code, ?int $excludeId = null): bool;

    public function isInUse(int $id): bool;

    public function save(Location $location): void;

    public function delete(int $id): void;
}
