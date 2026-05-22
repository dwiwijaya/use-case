<?php

declare(strict_types=1);

namespace App\Catalog\Item;

final readonly class Item
{
    public function __construct(
        public ?int $id,
        public string $sku,
        public string $name,
        public int $unitId,
    ) {}
}
