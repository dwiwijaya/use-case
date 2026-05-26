<?php

declare(strict_types=1);

namespace App\Inventory\Stock\Domain;

interface StockRepositoryInterface
{
    public function locationExists(int $locationId): bool;

    public function itemExists(int $itemId): bool;

    public function save(Stock $stock): void;

    public function delete(int $id): void;
}
