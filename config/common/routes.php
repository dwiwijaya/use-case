<?php

declare(strict_types=1);

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
                ->action(Web\Catalog\Items\Action::class)
                ->name('catalog.items'),
            Route::methods(['GET', 'POST'], '/inventory/locations')
                ->action(Web\Inventory\Locations\Action::class)
                ->name('inventory.locations'),
            Route::methods(['GET', 'POST'], '/inventory/stock')
                ->action(Web\Inventory\Stock\Action::class)
                ->name('inventory.stock'),
            Route::methods(['GET', 'POST'], '/sales/orders')
                ->action(Web\Sales\Orders\Action::class)
                ->name('sales.orders'),
        ),
    Route::get('/say[/{message}]')
        ->action(Web\Echo\Action::class)
        ->name('echo/say'),
];
