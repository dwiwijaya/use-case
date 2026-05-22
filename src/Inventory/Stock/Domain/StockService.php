<?php

declare(strict_types=1);

namespace App\Inventory\Stock\Domain;

final readonly class StockService
{
    public function __construct(
        private StockRepositoryInterface $stock,
    ) {}

    public function save(StockInput $input): void
    {
        $this->stock->save($input->toEntity());
    }

    public function delete(int $id): void
    {
        $this->stock->delete($id);
    }
}
