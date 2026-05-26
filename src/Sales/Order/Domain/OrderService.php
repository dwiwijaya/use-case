<?php

declare(strict_types=1);

namespace App\Sales\Order\Domain;

use DomainException;

use function array_keys;
use function array_sum;
use function date;
use function random_int;
use function sprintf;

final readonly class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    public function create(OrderInput $input): void
    {
        $lines = $input->collectLines();
        $aggregated = [];
        foreach ($lines as $line) {
            $aggregated[$line->itemId] = ($aggregated[$line->itemId] ?? 0) + $line->quantity;
        }

        $locationId = (int) $input->locationId;
        if (!$this->orders->locationExists($locationId)) {
            throw new DomainException('Lokasi order tidak ditemukan.');
        }

        $availableStock = $this->orders->getAvailableStock($locationId, array_keys($aggregated));
        foreach ($aggregated as $itemId => $quantity) {
            $available = $availableStock[$itemId] ?? 0;
            if ($available < $quantity) {
                throw new DomainException(
                    sprintf('Stok item #%d tidak cukup. Tersedia %d, diminta %d.', $itemId, $available, $quantity)
                );
            }
        }

        $this->orders->create(new Order(
            'SO-' . date('Ymd-His') . '-' . random_int(100, 999),
            $locationId,
            $input->customerName,
            date('Y-m-d H:i:s'),
            array_sum(array_map(static fn(OrderLine $line): int => $line->quantity, $lines)),
            $input->notes === '' ? null : $input->notes,
            $lines,
        ));
    }
}
