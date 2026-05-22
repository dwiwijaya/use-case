<?php

declare(strict_types=1);

namespace App\Web\HomePage;

use App\Catalog\CatalogViewRepositoryInterface;
use App\Inventory\InventoryViewRepositoryInterface;
use App\Sales\Order\OrderViewRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private CatalogViewRepositoryInterface $catalogRepository,
        private InventoryViewRepositoryInterface $inventoryRepository,
        private OrderViewRepositoryInterface $orderRepository,
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
