<?php

declare(strict_types=1);

namespace App\Sales\Order\ReadModel;

interface OrderViewRepositoryInterface
{
    /**
     * @return array{orders:int,total_items_sold:int}
     */
    public function getSummary(): array;

    /**
     * @return list<array{order_number:string,location_name:string,customer_name:string,ordered_at:string,total_items:string,items_summary:string}>
     */
    public function getRecentOrders(): array;

    /**
     * @return list<array{id:string,order_number:string,location_name:string,customer_name:string,ordered_at:string,total_items:string,notes:?string,items_summary:string}>
     */
    public function getOrders(): array;
}
