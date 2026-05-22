<?php

declare(strict_types=1);

namespace App\Sales\Order;

use App\Catalog\CatalogViewRepositoryInterface;
use App\Inventory\InventoryViewRepositoryInterface;
use DomainException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class PageAction
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private FormHydrator $formHydrator,
        private InventoryViewRepositoryInterface $inventoryViewRepository,
        private CatalogViewRepositoryInterface $catalogViewRepository,
        private OrderViewRepositoryInterface $orderViewRepository,
        private OrderService $orderService,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $selectedLocationId = isset($query['location']) && $query['location'] !== '' ? (int) $query['location'] : null;
        $form = OrderInput::withLocationId($selectedLocationId);

        if ($request->getMethod() === 'POST') {
            try {
                $this->formHydrator->populateFromPostAndValidate($form, $request, scope: 'order');
                if ($form->isValidated()) {
                    $form->validateLines();
                }

                if ($form->isValid()) {
                    $selectedLocationId = (int) $form->locationId;
                    $this->orderService->create($form);
                    return $this->render(
                        ['status' => 'order-created', 'location' => $selectedLocationId],
                        OrderInput::withLocationId($selectedLocationId),
                        [],
                        $selectedLocationId,
                    );
                }

                $selectedLocationId = $form->locationId === '' ? null : (int) $form->locationId;
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
                $selectedLocationId = $form->locationId === '' ? null : (int) $form->locationId;
            }
        }

        return $this->render($query, $form, $errors, $selectedLocationId);
    }

    /**
     * @param array<string,mixed> $query
     * @param list<string> $errors
     */
    private function render(array $query, OrderInput $form, array $errors, ?int $selectedLocationId): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'selectedLocationId' => $selectedLocationId,
            'form' => $form,
            'locations' => $this->inventoryViewRepository->getLocations(),
            'items' => $this->catalogViewRepository->getItems(),
            'stockRows' => $selectedLocationId === null ? [] : $this->inventoryViewRepository->getStockForLocation($selectedLocationId),
            'orders' => $this->orderViewRepository->getOrders(),
        ]);
    }
}
