<?php

declare(strict_types=1);

namespace App\Sales\Order;

final readonly class OrderLine
{
    public function __construct(
        public int $itemId,
        public int $quantity,
    ) {}
}
