<?php

declare(strict_types=1);

use App\Catalog\CatalogViewRepositoryInterface;
use App\Catalog\DbCatalogViewRepository;
use App\Catalog\Item\DbItemRepository;
use App\Catalog\Item\ItemRepositoryInterface;
use App\Catalog\Unit\DbUnitRepository;
use App\Catalog\Unit\UnitRepositoryInterface;
use App\Inventory\DbInventoryViewRepository;
use App\Inventory\InventoryViewRepositoryInterface;
use App\Inventory\Location\DbLocationRepository;
use App\Inventory\Location\LocationRepositoryInterface;
use App\Inventory\Stock\DbStockRepository;
use App\Inventory\Stock\StockRepositoryInterface;
use App\Sales\Order\DbOrderRepository;
use App\Sales\Order\DbOrderViewRepository;
use App\Sales\Order\OrderRepositoryInterface;
use App\Sales\Order\OrderViewRepositoryInterface;
use App\Shared\ApplicationParams;

/** @var array $params */

return [
    CatalogViewRepositoryInterface::class => DbCatalogViewRepository::class,
    UnitRepositoryInterface::class => DbUnitRepository::class,
    ItemRepositoryInterface::class => DbItemRepository::class,
    InventoryViewRepositoryInterface::class => DbInventoryViewRepository::class,
    LocationRepositoryInterface::class => DbLocationRepository::class,
    StockRepositoryInterface::class => DbStockRepository::class,
    OrderRepositoryInterface::class => DbOrderRepository::class,
    OrderViewRepositoryInterface::class => DbOrderViewRepository::class,

    ApplicationParams::class => [
        '__construct()' => [
            'name' => $params['application']['name'],
            'charset' => $params['application']['charset'],
            'locale' => $params['application']['locale'],
        ],
    ],
];
