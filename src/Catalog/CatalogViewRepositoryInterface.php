<?php

declare(strict_types=1);

namespace App\Catalog;

interface CatalogViewRepositoryInterface
{
    /**
     * @return list<array{id:string,name:string,symbol:string}>
     */
    public function getUnits(): array;

    /**
     * @return list<array{id:string,sku:string,name:string,unit_id:string,unit_name:string,unit_symbol:string}>
     */
    public function getItems(): array;

    /**
     * @return array{items:int,units:int}
     */
    public function getSummary(): array;
}
