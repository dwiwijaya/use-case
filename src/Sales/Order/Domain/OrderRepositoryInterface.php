<?php

declare(strict_types=1);

namespace App\Sales\Order\Domain;

interface OrderRepositoryInterface
{
    public function create(Order $order): void;
}
