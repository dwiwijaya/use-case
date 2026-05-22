<?php

declare(strict_types=1);

use App\Catalog\PageAction as CatalogPageAction;
use App\Inventory\Location\PageAction as InventoryLocationPageAction;
use App\Inventory\Stock\PageAction as InventoryStockPageAction;
use App\Sales\Order\PageAction as SalesOrderPageAction;
use App\Web;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()
        ->routes(
            Route::get('/')
                ->action(Web\HomePage\Action::class)
                ->name('home'),
            Route::methods(['GET', 'POST'], '/catalog/items')
                ->action(CatalogPageAction::class)
                ->name('catalog.items'),
            Route::methods(['GET', 'POST'], '/inventory/locations')
                ->action(InventoryLocationPageAction::class)
                ->name('inventory.locations'),
            Route::methods(['GET', 'POST'], '/inventory/stock')
                ->action(InventoryStockPageAction::class)
                ->name('inventory.stock'),
            Route::methods(['GET', 'POST'], '/sales/orders')
                ->action(SalesOrderPageAction::class)
                ->name('sales.orders'),
        ),
    Route::get('/say[/{message}]')
        ->action(Web\Echo\Action::class)
        ->name('echo/say'),
];
