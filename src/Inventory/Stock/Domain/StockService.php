<?php

declare(strict_types=1);

namespace App\Inventory\Stock\Domain;

use DomainException;

final readonly class StockService
{
    public function __construct(
        private StockRepositoryInterface $stock,
    ) {}

    public function save(StockInput $input): void
    {
        $stock = $input->toEntity();

        if (!$this->stock->locationExists($stock->locationId)) {
            throw new DomainException('Lokasi stok tidak ditemukan.');
        }

        if (!$this->stock->itemExists($stock->itemId)) {
            throw new DomainException('Item stok tidak ditemukan.');
        }

        $this->stock->save($stock);
    }

    public function delete(int $id): void
    {
        $this->stock->delete($id);
    }
}
