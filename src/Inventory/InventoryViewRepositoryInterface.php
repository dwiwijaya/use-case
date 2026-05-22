<?php

declare(strict_types=1);

namespace App\Inventory;

interface InventoryViewRepositoryInterface
{
    /**
     * @return list<array{id:string,code:string,name:string}>
     */
    public function getLocations(): array;

    /**
     * @return list<array{id:string,location_id:string,location_name:string,item_id:string,sku:string,item_name:string,unit_symbol:string,quantity:string}>
     */
    public function getStockRows(?int $locationId = null): array;

    /**
     * @return list<array{id:string,name:string,stock_rows:string,total_quantity:string}>
     */
    public function getStockByLocation(): array;

    /**
     * @return list<array{item_id:string,item_name:string,sku:string,quantity:string,unit_symbol:string}>
     */
    public function getStockForLocation(int $locationId): array;

    /**
     * @return array{locations:int,stock_rows:int,total_quantity:int}
     */
    public function getSummary(): array;
}
