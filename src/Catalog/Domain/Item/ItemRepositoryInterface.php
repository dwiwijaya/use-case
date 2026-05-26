<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Item;

interface ItemRepositoryInterface
{
    public function findById(int $id): ?Item;

    public function existsBySku(string $sku, ?int $excludeId = null): bool;

    public function unitExists(int $unitId): bool;

    public function isInUse(int $id): bool;

    public function save(Item $item): void;

    public function delete(int $id): void;
}
