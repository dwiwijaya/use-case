<?php

declare(strict_types=1);

namespace App\Catalog\Item;

final readonly class ItemService
{
    public function __construct(
        private ItemRepositoryInterface $items,
    ) {}

    public function save(ItemInput $input): void
    {
        $this->items->save($input->toEntity());
    }

    public function delete(int $id): void
    {
        $this->items->delete($id);
    }
}
