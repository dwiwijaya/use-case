<?php

declare(strict_types=1);

namespace App\Inventory\Stock;

final readonly class Stock
{
    public function __construct(
        public int $locationId,
        public int $itemId,
        public int $quantity,
    ) {}
}
