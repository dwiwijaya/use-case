<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Item;

use DomainException;

final readonly class ItemService
{
    public function __construct(
        private ItemRepositoryInterface $items,
    ) {}

    public function save(ItemInput $input): void
    {
        $item = $input->toEntity();

        if ($this->items->existsBySku($item->sku, $item->id)) {
            throw new DomainException('SKU item harus unik.');
        }

        if (!$this->items->unitExists($item->unitId)) {
            throw new DomainException('Unit item tidak ditemukan.');
        }

        $this->items->save($item);
    }

    public function delete(int $id): void
    {
        if ($this->items->isInUse($id)) {
            throw new DomainException('Item sudah dipakai di stok atau transaksi, jadi tidak bisa dihapus.');
        }

        $this->items->delete($id);
    }
}
