<?php

declare(strict_types=1);

namespace App\Web\HomePage;

use App\Catalog\CatalogRepository;
use App\Inventory\InventoryRepository;
use App\Sales\OrderRepository;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private CatalogRepository $catalogRepository,
        private InventoryRepository $inventoryRepository,
        private OrderRepository $orderRepository,
    ) {}

    public function __invoke(): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/template', [
            'catalogSummary' => $this->catalogRepository->getSummary(),
            'inventorySummary' => $this->inventoryRepository->getSummary(),
            'stockByLocation' => $this->inventoryRepository->getStockByLocation(),
            'salesSummary' => $this->orderRepository->getSummary(),
            'recentOrders' => $this->orderRepository->getRecentOrders(),
        ]);
    }
}
