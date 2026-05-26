<?php

declare(strict_types=1);

namespace App\Sales\Order\Domain;

interface OrderRepositoryInterface
{
    public function locationExists(int $locationId): bool;

    /**
     * @param list<int> $itemIds
     * @return array<int,int>
     */
    public function getAvailableStock(int $locationId, array $itemIds): array;

    public function create(Order $order): void;
}
