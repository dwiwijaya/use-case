<?php

declare(strict_types=1);

namespace App\Web\Sales\Orders;

use App\Catalog\CatalogRepository;
use App\Inventory\InventoryRepository;
use App\Sales\OrderRepository;
use App\Sales\OrderService;
use DomainException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

use function array_fill;
use function is_array;
use function trim;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
        private InventoryRepository $inventoryRepository,
        private CatalogRepository $catalogRepository,
        private OrderRepository $orderRepository,
        private OrderService $orderService,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $selectedLocationId = isset($query['location']) && $query['location'] !== '' ? (int) $query['location'] : null;
        $form = [
            'customer_name' => '',
            'location_id' => $selectedLocationId === null ? '' : (string) $selectedLocationId,
            'notes' => '',
            'lines' => array_fill(0, 5, ['item_id' => '', 'quantity' => '']),
        ];

        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];
            $rawLines = is_array($data['lines'] ?? null) ? $data['lines'] : [];
            $lines = [];

            $form = [
                'customer_name' => trim((string) ($data['customer_name'] ?? '')),
                'location_id' => trim((string) ($data['location_id'] ?? '')),
                'notes' => trim((string) ($data['notes'] ?? '')),
                'lines' => array_fill(0, 5, ['item_id' => '', 'quantity' => '']),
            ];

            foreach ($rawLines as $index => $line) {
                if (!is_array($line)) {
                    continue;
                }

                $itemId = trim((string) ($line['item_id'] ?? ''));
                $quantity = trim((string) ($line['quantity'] ?? ''));

                if (isset($form['lines'][$index])) {
                    $form['lines'][$index] = ['item_id' => $itemId, 'quantity' => $quantity];
                }

                if ($itemId === '' || $quantity === '') {
                    continue;
                }

                $lines[] = ['item_id' => (int) $itemId, 'quantity' => (int) $quantity];
            }

            try {
                if ($form['customer_name'] === '' || $form['location_id'] === '') {
                    throw new DomainException('Customer dan lokasi wajib diisi.');
                }

                foreach ($lines as $line) {
                    if ($line['quantity'] <= 0) {
                        throw new DomainException('Quantity order harus lebih dari nol.');
                    }
                }

                $this->orderService->create(
                    $form['customer_name'],
                    (int) $form['location_id'],
                    $form['notes'],
                    $lines
                );

                return $this->redirect([
                    'status' => 'order-created',
                    'location' => $form['location_id'],
                ]);
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
                $selectedLocationId = $form['location_id'] === '' ? null : (int) $form['location_id'];
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'selectedLocationId' => $selectedLocationId,
            'form' => $form,
            'locations' => $this->inventoryRepository->getLocations(),
            'items' => $this->catalogRepository->getItems(),
            'stockRows' => $selectedLocationId === null ? [] : $this->inventoryRepository->getStockForLocation($selectedLocationId),
            'orders' => $this->orderRepository->getOrders(),
        ]);
    }

    /**
     * @param array<string,string> $queryParameters
     */
    private function redirect(array $queryParameters): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('sales.orders', [], $queryParameters));
    }
}
