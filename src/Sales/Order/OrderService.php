<?php

declare(strict_types=1);

namespace App\Sales\Order;

use function array_sum;
use function date;
use function random_int;

final readonly class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    public function create(OrderInput $input): void
    {
        $lines = $input->collectLines();
        $this->orders->create(new Order(
            'SO-' . date('Ymd-His') . '-' . random_int(100, 999),
            (int) $input->locationId,
            $input->customerName,
            date('Y-m-d H:i:s'),
            array_sum(array_map(static fn(OrderLine $line): int => $line->quantity, $lines)),
            $input->notes === '' ? null : $input->notes,
            $lines,
        ));
    }
}
