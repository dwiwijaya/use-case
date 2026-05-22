<?php

declare(strict_types=1);

namespace App\Sales\Order\Domain;

final readonly class Order
{
    /**
     * @param list<OrderLine> $lines
     */
    public function __construct(
        public string $orderNumber,
        public int $locationId,
        public string $customerName,
        public string $orderedAt,
        public int $totalItems,
        public ?string $notes,
        public array $lines,
    ) {}
}
