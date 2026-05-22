<?php

declare(strict_types=1);

use App\Catalog\Domain\Item\ItemRepositoryInterface;
use App\Catalog\Domain\Unit\UnitRepositoryInterface;
use App\Catalog\Infrastructure\Persistence\DbCatalogViewRepository;
use App\Catalog\Infrastructure\Persistence\DbItemRepository;
use App\Catalog\Infrastructure\Persistence\DbUnitRepository;
use App\Catalog\ReadModel\CatalogViewRepositoryInterface;
use App\Inventory\Location\Domain\LocationRepositoryInterface;
use App\Inventory\Location\Infrastructure\Persistence\DbLocationRepository;
use App\Inventory\ReadModel\DbInventoryViewRepository;
use App\Inventory\ReadModel\InventoryViewRepositoryInterface;
use App\Inventory\Stock\Domain\StockRepositoryInterface;
use App\Inventory\Stock\Infrastructure\Persistence\DbStockRepository;
use App\Sales\Order\Domain\OrderRepositoryInterface;
use App\Sales\Order\Infrastructure\Persistence\DbOrderRepository;
use App\Sales\Order\Infrastructure\Persistence\DbOrderViewRepository;
use App\Sales\Order\ReadModel\OrderViewRepositoryInterface;
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
