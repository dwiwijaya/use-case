<?php

declare(strict_types=1);

namespace App\Inventory\Location;

interface LocationRepositoryInterface
{
    public function findById(int $id): ?Location;

    public function save(Location $location): void;

    public function delete(int $id): void;
}
